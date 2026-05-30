import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/club.dart';
import '../providers/app_provider.dart';
import 'events_screen.dart';
import 'checkout_screen.dart';

class YearsScreen extends StatelessWidget {
  final Club club;
  const YearsScreen({super.key, required this.club});

  @override
  Widget build(BuildContext context) {
    final appProvider = Provider.of<AppProvider>(context);
    final years = appProvider.getEventYears();
    
    // Extract active upcoming events created by the president
    final activeEvents = appProvider.events.where((e) => e.status == 'active').toList();

    return Scaffold(
      appBar: AppBar(
        title: Text(club.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: const Color(0xFF111827),
        elevation: 0,
      ),
      body: Container(
        color: const Color(0xFF030712),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Section 1: Active Upcoming Event Alerts
            if (activeEvents.isNotEmpty) ...[
              const Row(
                children: [
                  Icon(Icons.stars, color: Colors.amber, size: 18),
                  SizedBox(width: 8),
                  Text(
                    'Active Registration Portals',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Colors.white),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              ...activeEvents.map((evt) => GestureDetector(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => CheckoutScreen(event: evt, club: club)),
                  );
                },
                child: Card(
                  margin: const EdgeInsets.only(bottom: 16),
                  shape: RoundedRectangleBorder(
                    side: const BorderSide(color: Color(0xFF4F46E5), width: 1.5),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF4F46E5).withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Text(
                                  'REGISTRATION OPEN',
                                  style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFF818CF8)),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                evt.title,
                                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${evt.formattedDate} • ${evt.venue}',
                                style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              evt.price > 0 ? '₹${evt.price.toStringAsFixed(2)}' : 'FREE',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF10B981)),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${evt.spotsLeft} spots left',
                              style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              )),
              const Divider(color: Color(0xFF1F2937), height: 32, thickness: 1.5),
            ],

            // Section 2: Historical Activity Reports by Academic Year
            const Row(
              children: [
                Icon(Icons.history, color: Color(0xFF6366F1), size: 18),
                SizedBox(width: 8),
                Text(
                  'Historical Portfolios (PDF Extracted)',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Colors.white),
                ),
              ],
            ),
            const SizedBox(height: 16),

            if (years.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 48.0),
                  child: Text(
                    'No historical report data found.',
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                  ),
                ),
              )
            else
              ...years.map((yr) => Container(
                margin: const EdgeInsets.only(bottom: 12),
                child: Card(
                  child: ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    leading: Container(
                      height: 40,
                      width: 40,
                      decoration: BoxDecoration(
                        color: const Color(0xFF1F2937),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.picture_as_pdf, color: Color(0xFFEF4444), size: 20),
                    ),
                    title: Text(
                      'Tenure Report ($yr)',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.white),
                    ),
                    subtitle: const Text(
                      'Extracted past events and photo galleries.',
                      style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                    ),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFF64748B)),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => EventsScreen(
                            club: club,
                            yearLabel: yr,
                            events: appProvider.getEventsForYear(yr),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              )),
          ],
        ),
      ),
    );
  }
}
