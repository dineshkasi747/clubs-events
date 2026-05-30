<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EventsFromJsonSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('events.json');

        if (!File::exists($jsonPath)) {
            $this->command->warn("events.json not found at $jsonPath. Skipping event seeding.");
            return;
        }

        $json = File::get($jsonPath);
        $eventsData = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON format in events.json. Skipping event seeding.");
            return;
        }

        foreach ($eventsData as $data) {
            $title = $data['title'];
            $description = $data['description'] ?? '';
            
            // Intelligent keyword-based club matching
            $club = null;

            if (isset($data['club_name'])) {
                $club = Club::where('name', $data['club_name'])->first();
            }

            if (!$club) {
                $club = $this->guessClubByKeywords($title . ' ' . $description);
            }

            // Fallback to first club
            if (!$club) {
                $club = Club::first();
            }

            if (!$club) {
                $this->command->error("No clubs found. Please run UserSeeder before seeding events.");
                return;
            }

            // Create historical event
            $event = Event::create([
                'club_id' => $club->id,
                'title' => $title,
                'description' => $description,
                'venue' => $data['place'] ?? null, // Map place to venue for backwards compatibility
                'place' => $data['place'] ?? null,
                'date_string' => $data['date'] ?? null,
                'volunteers' => $data['volunteers'] ?? null,
                'start_time' => null, // null for historical events
                'end_time' => null,
                'price' => 0.00, // Historical events are free/completed in terms of active transactions
                'status' => 'completed', // historical events are completed
                'capacity' => 100,
            ]);

            // Create event images
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imagePath) {
                    // Check if path has images/ prefix, convert it if needed
                    $path = Str::startsWith($imagePath, '/') ? $imagePath : '/' . $imagePath;
                    
                    EventImage::create([
                        'event_id' => $event->id,
                        'path' => $path,
                    ]);
                }
            }
        }
    }

    protected function guessClubByKeywords(string $text): ?Club
    {
        $keywordsMap = [
            'Coding Club' => ['code', 'hack', 'web', 'app', 'programming', 'developer', 'python', 'java', 'software'],
            'Robotics Club' => ['robot', 'mech', 'arena', 'rover', 'arduino', 'sensor', 'autonomous'],
            'Music Society' => ['music', 'symphony', 'band', 'acoustic', 'singing', 'concert', 'instrumental', 'guitar'],
            'Astronomy Club' => ['star', 'sky', 'planet', 'observatory', 'astronomy', 'telescope', 'galaxy', 'moon'],
            'Debate Club' => ['debate', 'panel', 'discussion', 'ethics', 'speech', 'deliberation'],
            'Drama Society' => ['drama', 'theater', 'play', 'act', 'shakespeare', 'stage', 'performance'],
            'Green Earth Club' => ['green', 'earth', 'eco', 'tree', 'plantation', 'environment', 'recycling', 'climate'],
            'Finance & Investment Club' => ['finance', 'investment', 'stock', 'crypto', 'trading', 'portfolio', 'market'],
            'Art & Photography Club' => ['art', 'photo', 'paint', 'camera', 'snap', 'gallery', 'drawing', 'lens'],
            'Literature Club' => ['book', 'lit', 'read', 'write', 'poem', 'novel', 'literature'],
            'Chess Guild' => ['chess', 'board', 'grandmaster', 'checkmate', 'knight', 'rook', 'pawn'],
            'Film & Media Society' => ['film', 'media', 'video', 'cinema', 'movie', 'recording', 'script'],
            'Biotech Association' => ['bio', 'chem', 'dna', 'lab', 'biology', 'chemistry', 'science'],
            'Sports Club' => ['sport', 'game', 'play', 'match', 'run', 'cricket', 'football', 'athletic', 'tournament'],
            'Gaming League' => ['game', 'play', 'esport', 'xbox', 'playstation', 'pc', 'tournament'],
            'Dance Troupe' => ['dance', 'beat', 'step', 'choreography', 'rhythm'],
            'Culinary Arts Club' => ['food', 'cook', 'bake', 'culinary', 'chef', 'dish', 'recipe'],
            'Fashion Design Club' => ['fashion', 'dress', 'style', 'clothing', 'runway', 'designer'],
            'Aerospace Club' => ['aero', 'fly', 'space', 'rocket', 'drone', 'aviation'],
            'Mathematics Circle' => ['math', 'number', 'calculus', 'algebra', 'equation', 'geometry']
        ];

        foreach ($keywordsMap as $clubName => $keywords) {
            foreach ($keywords as $word) {
                if (stripos($text, $word) !== false) {
                    $club = Club::where('name', $clubName)->first();
                    if ($club) {
                        return $club;
                    }
                }
            }
        }

        return null;
    }
}
