class Event {
  final int id;
  final int clubId;
  final String title;
  final String? description;
  final String venue;
  final String formattedDate;
  final double price;
  final String status;
  final int capacity;
  final int spotsLeft;
  final String? imagePath;
  final String? volunteers;
  final List<String> allImages;

  Event({
    required this.id,
    required this.clubId,
    required this.title,
    this.description,
    required this.venue,
    required this.formattedDate,
    required this.price,
    required this.status,
    required this.capacity,
    required this.spotsLeft,
    this.imagePath,
    this.volunteers,
    required this.allImages,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    // Safely extract first image path if it exists
    String? firstImage;
    List<String> imagesList = [];
    
    if (json['images'] != null && (json['images'] as List).isNotEmpty) {
      firstImage = json['images'][0]['path'];
      imagesList = (json['images'] as List)
          .map((img) => (img['path'] ?? '').toString())
          .where((path) => path.isNotEmpty)
          .toList();
    }

    return Event(
      id: json['id'],
      clubId: json['club_id'],
      title: json['title'] ?? '',
      description: json['description'],
      venue: json['venue'] ?? json['place'] ?? 'Main Campus',
      formattedDate: json['formatted_date'] ?? json['date_string'] ?? 'Date Pending',
      price: double.parse((json['price'] ?? 0.00).toString()),
      status: json['status'] ?? 'completed',
      capacity: json['capacity'] ?? 100,
      spotsLeft: json['spots_remaining'] ?? json['capacity'] ?? 100,
      imagePath: firstImage,
      volunteers: json['volunteers']?.toString(),
      allImages: imagesList,
    );
  }
}
