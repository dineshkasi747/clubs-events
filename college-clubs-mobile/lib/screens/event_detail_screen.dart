import 'package:flutter/material.dart';
import '../models/event.dart';
import '../core/api_client.dart';

class EventDetailScreen extends StatefulWidget {
  final Event event;
  const EventDetailScreen({super.key, required this.event});

  @override
  State<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _EventDetailScreenState extends State<EventDetailScreen> {
  int _currentImageIndex = 0;

  @override
  Widget build(BuildContext context) {
    final evt = widget.event;

    // Compute dynamic full URLs for all event images
    final List<String> imageUrls = evt.allImages
        .map((path) => '${ApiClient.baseUrl.replaceAll('/api', '')}$path')
        .toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Event details', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: const Color(0xFF111827),
        elevation: 0,
      ),
      body: Container(
        color: const Color(0xFF030712),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Image Carousel Banner
              Container(
                height: 250,
                color: const Color(0xFF1F2937),
                child: imageUrls.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.photo_library, color: Color(0xFF64748B), size: 48),
                            SizedBox(height: 8),
                            Text('No media attached to this event', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                          ],
                        ),
                      )
                    : Stack(
                        children: [
                          PageView.builder(
                            itemCount: imageUrls.length,
                            onPageChanged: (index) {
                              setState(() {
                                _currentImageIndex = index;
                              });
                            },
                            itemBuilder: (context, index) {
                              return Image.network(
                                imageUrls[index],
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) => const Center(
                                  child: Icon(Icons.broken_image, color: Color(0xFF64748B), size: 48),
                                ),
                              );
                            },
                          ),
                          // Dynamic Dots Indicator for multiple photos
                          if (imageUrls.length > 1)
                            Positioned(
                              bottom: 16,
                              left: 0,
                              right: 0,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: List.generate(
                                  imageUrls.length,
                                  (index) => Container(
                                    margin: const EdgeInsets.symmetric(horizontal: 4),
                                    width: _currentImageIndex == index ? 10 : 6,
                                    height: _currentImageIndex == index ? 10 : 6,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: _currentImageIndex == index
                                          ? const Color(0xFF6366F1)
                                          : Colors.white.withOpacity(0.4),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
              ),

              // 2. Event Header Info
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withOpacity(0.15),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            'COMPLETED PORTFOLIO',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF34D399)),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text(
                      evt.title,
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Colors.white, height: 1.2),
                    ),
                    const SizedBox(height: 24),

                    // 3. Details Card Block
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                        ),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.white.withOpacity(0.05)),
                      ),
                      child: Column(
                        children: [
                          _buildDetailRow(
                            Icons.place,
                            'Venue / Location',
                            evt.venue,
                          ),
                          const Divider(color: Color(0xFF334155), height: 24),
                          _buildDetailRow(
                            Icons.calendar_today,
                            'Event Date & Time',
                            evt.formattedDate,
                          ),
                          const Divider(color: Color(0xFF334155), height: 24),
                          _buildDetailRow(
                            Icons.people,
                            'Volunteers Count',
                            '${evt.volunteers ?? 'N/A'} volunteers involved',
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 28),

                    // 4. Description Section
                    const Text(
                      'Activity Description',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      evt.description ?? 'No historical event report description is recorded.',
                      style: const TextStyle(fontSize: 14, color: Color(0xFF94A3B8), height: 1.6),
                    ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(IconData icon, String title, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          height: 36,
          width: 36,
          decoration: BoxDecoration(
            color: const Color(0xFF6366F1).withOpacity(0.15),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: const Color(0xFF818CF8), size: 18),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(fontSize: 12, color: Color(0xFFE2E8F0), fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
