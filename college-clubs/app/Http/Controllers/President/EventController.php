<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $club = Auth::user()->club;
        $events = $club
            ? Event::where('club_id', $club->id)->orderBy('start_time', 'desc')->get()
            : collect();

        return view('president.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('president.events.create', ['event' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $club = Auth::user()->club;
        if (!$club) {
            return redirect()->route('president.dashboard')->with('error', 'You must be assigned to a club to create events.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,draft,completed,cancelled'],
            'image' => ['nullable', 'image', 'max:2048'], // max 2MB
        ]);

        // Create the event
        $event = Event::create([
            'club_id' => $club->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'venue' => $validated['venue'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'price' => $validated['price'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
        ]);

        // Upload and save image if present
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            EventImage::create([
                'event_id' => $event->id,
                'path' => '/storage/' . $path,
            ]);
        }

        // Trigger FCM Push Notification if the event is published (active)
        if ($event->status === 'active') {
            \App\Services\FcmService::broadcastToStudents(
                "New Event in {$club->name}!",
                "Check out '{$event->title}' scheduled at {$event->venue}. Register now!",
                ['event_id' => $event->id, 'type' => 'new_event']
            );
        }

        return redirect()->route('president.events.index')->with('success', "Event '{$event->title}' has been created successfully!");
    }

    public function edit(Event $event): View
    {
        return view('president.events.create', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,draft,completed,cancelled'],
            'image' => ['nullable', 'image', 'max:2048'], // max 2MB
        ]);

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'venue' => $validated['venue'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'price' => $validated['price'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old images if they exist
            foreach ($event->images as $img) {
                $relative = str_replace('/storage/', '', $img->path);
                Storage::disk('public')->delete($relative);
                $img->delete();
            }

            $path = $request->file('image')->store('events', 'public');
            EventImage::create([
                'event_id' => $event->id,
                'path' => '/storage/' . $path,
            ]);
        }

        return redirect()->route('president.events.index')->with('success', "Event '{$event->title}' has been updated successfully!");
    }

    public function uploadPdf(Request $request): RedirectResponse
    {
        $club = Auth::user()->club;
        if (!$club) {
            return redirect()->route('president.events.index')->with('error', 'You must be assigned to a club to upload event reports.');
        }

        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'], // max 10MB
        ]);

        $file = $request->file('pdf');
        $pdfPath = $file->getRealPath();
        $outputDir = storage_path('app/public/events');

        $pythonScript = base_path('app/Services/pdf_parser.py');
        $prefix = time() . '_' . $club->id . '_';
        
        // Build the command safely (capturing standard error as well)
        $command = "python " . escapeshellarg($pythonScript) . " --pdf " . escapeshellarg($pdfPath) . " --out-dir " . escapeshellarg($outputDir) . " --prefix " . escapeshellarg($prefix) . " 2>&1";

        // Execute command
        $output = shell_exec($command);
        if (empty($output)) {
            return redirect()->route('president.events.index')->with('error', 'Failed to extract events. The parser output was empty.');
        }

        // Clean up output encoding to prevent non-UTF-8 character crashes during json_decode
        $outputUtf8 = mb_convert_encoding($output, 'UTF-8', 'UTF-8,CP1252,ISO-8859-1,ASCII');
        $eventsData = json_decode($outputUtf8, true);
        if (json_last_error() !== JSON_ERROR_NONE || isset($eventsData['error'])) {
            $errMsg = $eventsData['error'] ?? 'Parser Traceback / Output: ' . trim($output);
            return redirect()->route('president.events.index')->with('error', 'Parser Error: ' . $errMsg);
        }

        if (empty($eventsData)) {
            return redirect()->route('president.events.index')->with('error', 'No events found in the uploaded PDF. Please make sure it matches the activity report format.');
        }

        $importedCount = 0;
        foreach ($eventsData as $data) {
            // Reformat date string if possible, or fallback to today
            $dateStr = $data['date'] ?? null;
            $parsedDate = null;
            if ($dateStr) {
                try {
                    $parsedDate = \Carbon\Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    $parsedDate = now();
                }
            } else {
                $parsedDate = now();
            }

            // Create Event
            $event = Event::create([
                'club_id' => $club->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'venue' => $data['place'] ?? 'Main Campus',
                'place' => $data['place'] ?? 'Main Campus',
                'start_time' => $parsedDate,
                'end_time' => $parsedDate->copy()->addHours(2), // standard 2 hour duration
                'date_string' => $dateStr,
                'volunteers' => $data['volunteers'] ?? null,
                'status' => 'completed',
                'price' => 0.00,
                'capacity' => 100,
            ]);

            // Save extracted images
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imgName) {
                    EventImage::create([
                        'event_id' => $event->id,
                        'path' => '/storage/events/' . $imgName,
                    ]);
                }
            }

            $importedCount++;
        }

        return redirect()->route('president.events.index')->with('success', "Successfully imported {$importedCount} historical events from your activity report!");
    }

    public function destroy(Event $event): RedirectResponse
    {
        $title = $event->title;
        
        // Delete related images from storage
        foreach ($event->images as $img) {
            $relative = str_replace('/storage/', '', $img->path);
            Storage::disk('public')->delete($relative);
        }

        $event->delete();

        return redirect()->route('president.events.index')->with('success', "Event '{$title}' has been deleted.");
    }
}
