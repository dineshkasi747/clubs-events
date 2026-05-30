import 'package:flutter/material.dart';
import '../models/club.dart';
import '../models/event.dart';
import '../core/api_client.dart';
import 'event_detail_screen.dart';

class EventsScreen extends StatelessWidget {
  final Club club;
  final String yearLabel;
  final List<Event> events;

  const EventsScreen({
    super.key,
    required this.club,
    required this.yearLabel,
    required this.events,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Activity Report $yearLabel', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: const Color(0xFF111827),
        elevation: 0,
      ),
      body: Container(
        color: const Color(0xFF030712),
        child: events.isEmpty
            ? const Center(
                child: const Text('No historical events parsed for this year.', style: TextStyle(color: Color(0xFF94A3B8))),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: events.length,
                itemBuilder: (context, index) {
                  final evt = events[index];
                  
                  // Compute dynamic full banner URL from Laravel backend
                  final String? fullImageUrl = evt.imagePath != null
                      ? '${ApiClient.baseUrl.replaceAll('/api', '')}${evt.imagePath}'
                      : null;

                  return GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => EventDetailScreen(event: evt),
                        ),
                      );
                    },
                    child: Card(
                      margin: const EdgeInsets.only(bottom: 20),
                      clipBehavior: Clip.antiAlias,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Dynamic Event Image
                        Container(
                          height: 180,
                          color: const Color(0xFF374151),
                          child: fullImageUrl != null
                              ? Image.network(
                                  fullImageUrl,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) => const Center(
                                    child: Icon(Icons.broken_image, color: const Color(0xFF64748B), size: 40),
                                  ),
                                )
                              : const Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      const Icon(Icons.photo_library, color: Color(0xFF64748B), size: 36),
                                      const SizedBox(height: 4),
                                      const Text('No media attached', style: TextStyle(fontSize: 10, color: Color(0xFF64748B))),
                                    ],
                                  ),
                                ),
                        ),

                        // Card Body
                        Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                evt.title,
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                evt.description ?? 'No event description entered.',
                                style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8), height: 1.4),
                              ),
                              const SizedBox(height: 16),
                              
                              // Detail tags grid
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF0F172A),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Column(
                                  children: [
                                    // Place details
                                    Row(
                                      children: [
                                        const Icon(Icons.place, size: 14, color: Color(0xFF6366F1)),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            evt.venue,
                                            style: const TextStyle(fontSize: 11, color: Color(0xFFCBD5E1)),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    
                                    // Date details
                                    Row(
                                      children: [
                                        const Icon(Icons.calendar_today, size: 14, color: Color(0xFF6366F1)),
                                        const SizedBox(width: 8),
                                        Text(
                                          evt.formattedDate,
                                          style: const TextStyle(fontSize: 11, color: Color(0xFFCBD5E1)),
                                        ),
                                      ],
                                    ),
                                    
                                    // Volunteers details
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        const Icon(Icons.people, size: 14, color: Color(0xFF6366F1)),
                                        const SizedBox(width: 8),
                                        Text(
                                          'Volunteers involved: ${evt.volunteers ?? 'N/A'}',
                                          style: const TextStyle(fontSize: 11, color: Color(0xFFCBD5E1)),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
              ),
      ),
    );
  }
}
