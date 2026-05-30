import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/club.dart';
import '../models/event.dart';
import '../core/api_client.dart';

class CheckoutScreen extends StatefulWidget {
  final Event event;
  final Club club;

  const CheckoutScreen({
    super.key,
    required this.event,
    required this.club,
  });

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  String _selectedMethod = 'card'; // 'card', 'stripe', 'paypal'
  
  // Simulated Card controllers
  final _nameController = TextEditingController();
  final _numberController = TextEditingController();
  final _expiryController = TextEditingController();
  final _cvvController = TextEditingController();

  bool _isProcessing = false;

  @override
  void dispose() {
    _nameController.dispose();
    _numberController.dispose();
    _expiryController.dispose();
    _cvvController.dispose();
    super.dispose();
  }

  void _processBooking() async {
    // If paid and using card, validate form
    if (widget.event.price > 0 && _selectedMethod == 'card') {
      if (!_formKey.currentState!.validate()) {
        return;
      }
    }

    setState(() {
      _isProcessing = true;
    });

    try {
      // 1. Create Registration on backend
      final response = await ApiClient.client.post(
        '/events/${widget.event.id}/register',
      );

      setState(() {
        _isProcessing = false;
      });

      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        final registrationId = data['registration']['id'];
        
        if (widget.event.price > 0) {
          // Paid event: Launch Razorpay Web Checkout
          final razorpayOrder = data['razorpay_order'];
          final razorpayKey = data['razorpay_key'];
          final orderId = razorpayOrder['id'];
          final amount = razorpayOrder['amount'].toString();
          
          final String baseUrl = ApiClient.baseUrl.replaceAll('/api', '');
          final String checkoutUrl = "$baseUrl/payments/checkout"
              "?registration_id=$registrationId"
              "&order_id=$orderId"
              "&amount=$amount"
              "&key=$razorpayKey";
              
          final Uri uri = Uri.parse(checkoutUrl);
          
          if (await canLaunchUrl(uri)) {
            await launchUrl(uri, mode: LaunchMode.externalApplication);
            // Open the verification dialog
            _showVerificationDialog(registrationId);
          } else {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Failed to open payment gateway. Please try again.'),
                backgroundColor: Colors.redAccent,
                behavior: SnackBarBehavior.floating,
              ),
            );
          }
        } else {
          // Free event: instantly approved
          _showSuccessDialog();
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to initiate registration.'),
            backgroundColor: Colors.redAccent,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isProcessing = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: Colors.redAccent,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _showVerificationDialog(int registrationId) {
    bool isVerifying = false;
    String statusMessage = "Please authorize the transaction in the payment window, and click 'Verify Ticket Status' once complete.";
    
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return Dialog(
              backgroundColor: const Color(0xFF111827),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Icon
                    Container(
                      height: 64,
                      width: 64,
                      decoration: BoxDecoration(
                        color: const Color(0xFF4F46E5).withOpacity(0.15),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        isVerifying ? Icons.sync : Icons.security,
                        color: const Color(0xFF818CF8),
                        size: 32,
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'Verify Payment Status',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      statusMessage,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8), height: 1.4),
                    ),
                    const SizedBox(height: 24),
                    
                    if (isVerifying)
                      const CircularProgressIndicator(color: Color(0xFF4F46E5))
                    else ...[
                      ElevatedButton(
                        onPressed: () async {
                          setDialogState(() {
                            isVerifying = true;
                            statusMessage = "Contacting verification guard on the backend...";
                          });
                          
                          // Check registration status on backend
                          try {
                            final response = await ApiClient.client.get('/registrations');
                            bool isApproved = false;
                            
                            if (response.statusCode == 200) {
                              final List regs = response.data;
                              final match = regs.firstWhere(
                                (r) => r['id'] == registrationId,
                                orElse: () => null,
                              );
                              if (match != null && match['status'] == 'approved') {
                                isApproved = true;
                              }
                            }
                            
                            if (isApproved) {
                              Navigator.of(context).pop(); // pop this dialog
                              _showSuccessDialog(); // show success ticket
                            } else {
                              setDialogState(() {
                                isVerifying = false;
                                statusMessage = "⚠️ Verification pending. We couldn't confirm your transaction yet. Please ensure you finished payment in the browser wizard.";
                              });
                            }
                          } catch (e) {
                            setDialogState(() {
                              isVerifying = false;
                              statusMessage = "🔴 Network error. Verification failed. Please try again.";
                            });
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4F46E5),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          minimumSize: const Size(double.infinity, 44),
                        ),
                        child: const Text('Verify Ticket Status', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
                      ),
                      const SizedBox(height: 10),
                      TextButton(
                        onPressed: () {
                          Navigator.of(context).pop();
                        },
                        child: const Text('Cancel / Close', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13)),
                      ),
                    ]
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showSuccessDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return Dialog(
          backgroundColor: const Color(0xFF111827),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Success Badge
                Container(
                  height: 72,
                  width: 72,
                  decoration: BoxDecoration(
                    color: const Color(0xFF10B981).withOpacity(0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check_circle,
                    color: Color(0xFF10B981),
                    size: 48,
                  ),
                ),
                const SizedBox(height: 20),
                const Text(
                  'Registration Confirmed!',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'You have successfully registered for ${widget.event.title}.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF94A3B8),
                  ),
                ),
                const SizedBox(height: 24),
                // Premium simulated ticket details
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1F2937),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF374151), width: 1),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('EVENT', style: TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
                          Text(widget.club.name.toUpperCase(), style: const TextStyle(fontSize: 10, color: Color(0xFF818CF8), fontWeight: FontWeight.bold)),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        widget.event.title,
                        textAlign: TextAlign.center,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                      const Divider(color: Color(0xFF374151), height: 20),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('DATE', style: TextStyle(fontSize: 9, color: Color(0xFF64748B))),
                              const SizedBox(height: 4),
                              Text(widget.event.formattedDate, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white)),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              const Text('VENUE', style: TextStyle(fontSize: 9, color: Color(0xFF64748B))),
                              const SizedBox(height: 4),
                              Text(widget.event.venue, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white)),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () {
                    // Navigate back to clubs
                    Navigator.of(context).pop(); // Dismiss dialog
                    Navigator.of(context).pop(); // Pop checkout screen
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    minimumSize: const Size(double.infinity, 48),
                  ),
                  child: const Text(
                    'Return to Portfolio',
                    style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final String? fullImageUrl = widget.event.imagePath != null
        ? '${ApiClient.baseUrl.replaceAll('/api', '')}${widget.event.imagePath}'
        : null;

    final isPaid = widget.event.price > 0;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Checkout Registration', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: const Color(0xFF111827),
        elevation: 0,
      ),
      body: Container(
        color: const Color(0xFF030712),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Event Hero details
              Card(
                clipBehavior: Clip.antiAlias,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (fullImageUrl != null)
                      SizedBox(
                        height: 140,
                        child: Image.network(
                          fullImageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) => const Center(
                            child: Icon(Icons.broken_image, color: Color(0xFF475569), size: 40),
                          ),
                        ),
                      ),
                    Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF818CF8).withOpacity(0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              widget.club.name.toUpperCase(),
                              style: const TextStyle(fontSize: 10, color: Color(0xFF818CF8), fontWeight: FontWeight.bold),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            widget.event.title,
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              const Icon(Icons.location_on, size: 14, color: Color(0xFF94A3B8)),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  widget.event.venue,
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              const Icon(Icons.calendar_today, size: 14, color: Color(0xFF94A3B8)),
                              const SizedBox(width: 6),
                              Text(
                                widget.event.formattedDate,
                                style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Pricing Summary
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Total Registration Fee',
                        style: const TextStyle(fontSize: 14, color: Color(0xFFCBD5E1)),
                      ),
                      Text(
                        isPaid ? '₹${widget.event.price.toStringAsFixed(2)}' : 'FREE',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF10B981),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Payment options & forms (only if paid)
              if (isPaid) ...[
                const Text(
                  'Select Payment Mode',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                ),
                const SizedBox(height: 12),
                
                // Payment Method Selector
                Row(
                  children: [
                    _buildMethodTab('card', Icons.credit_card, 'Card'),
                    const SizedBox(width: 8),
                    _buildMethodTab('upi', Icons.phone_android, 'UPI / PhonePe'),
                    const SizedBox(width: 8),
                    _buildMethodTab('paypal', Icons.payment, 'PayPal'),
                  ],
                ),
                const SizedBox(height: 20),

                // Form or explanation based on selection
                if (_selectedMethod == 'card')
                  Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        _buildInputField(
                          controller: _nameController,
                          label: 'Cardholder Name',
                          hint: 'John Doe',
                          icon: Icons.person,
                          validator: (v) => v == null || v.isEmpty ? 'Cardholder name is required' : null,
                        ),
                        const SizedBox(height: 12),
                        _buildInputField(
                          controller: _numberController,
                          label: 'Credit Card Number',
                          hint: '4111 2222 3333 4444',
                          icon: Icons.credit_card_outlined,
                          keyboardType: TextInputType.number,
                          validator: (v) => v == null || v.length < 16 ? 'Enter a valid credit card number' : null,
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              flex: 2,
                              child: _buildInputField(
                                controller: _expiryController,
                                label: 'Expiry Date',
                                hint: 'MM/YY',
                                icon: Icons.calendar_today_outlined,
                                keyboardType: TextInputType.datetime,
                                validator: (v) => v == null || !v.contains('/') ? 'MM/YY required' : null,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              flex: 1,
                              child: _buildInputField(
                                controller: _cvvController,
                                label: 'CVV',
                                hint: '***',
                                icon: Icons.lock_outline,
                                keyboardType: TextInputType.number,
                                obscure: true,
                                validator: (v) => v == null || v.length < 3 ? 'Invalid' : null,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  )
                else if (_selectedMethod == 'upi')
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E293B).withOpacity(0.4),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFF334155), width: 1),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.mobile_friendly, color: Color(0xFF818CF8), size: 22),
                            SizedBox(width: 8),
                            Text(
                              'PhonePe UPI Payment',
                              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Select your preferred UPI app to pay:',
                          style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildUpiAppLogo('PhonePe', const Color(0xFF5F259F), Icons.phone_iphone),
                            _buildUpiAppLogo('GPay', const Color(0xFF1D83E4), Icons.payment_outlined),
                            _buildUpiAppLogo('Paytm', const Color(0xFF00B9F5), Icons.account_balance_wallet),
                          ],
                        ),
                        const SizedBox(height: 20),
                        const Text(
                          'Or enter your UPI ID:',
                          style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          initialValue: 'student@ybl',
                          style: const TextStyle(fontSize: 13, color: Colors.white),
                          decoration: InputDecoration(
                            hintText: 'username@bank',
                            hintStyle: const TextStyle(color: Color(0xFF475569), fontSize: 13),
                            filled: true,
                            fillColor: const Color(0xFF0F172A),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xFF334155)),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xFF334155)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E293B).withOpacity(0.4),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFF334155), width: 1),
                    ),
                    child: Column(
                      children: [
                        Icon(
                          Icons.paypal,
                          color: const Color(0xFF818CF8),
                          size: 40,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'You will be redirected to $_selectedMethod secure portal to authorize the transaction.',
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8), height: 1.4),
                        ),
                      ],
                    ),
                  ),
                const SizedBox(height: 24),
              ],

              // Checkout Button
              ElevatedButton(
                onPressed: _isProcessing ? null : _processBooking,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  backgroundColor: const Color(0xFF4F46E5),
                  disabledBackgroundColor: const Color(0xFF4F46E5).withOpacity(0.5),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 8,
                ),
                child: _isProcessing
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : Text(
                        isPaid ? 'Pay & Secure Ticket' : 'Claim Free Entry Ticket',
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMethodTab(String method, IconData icon, String label) {
    final isSelected = _selectedMethod == method;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() {
            _selectedMethod = method;
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFF4F46E5).withOpacity(0.15) : const Color(0xFF1E293B).withOpacity(0.4),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? const Color(0xFF4F46E5) : const Color(0xFF334155),
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Column(
            children: [
              Icon(icon, color: isSelected ? const Color(0xFF818CF8) : const Color(0xFF94A3B8), size: 20),
              const SizedBox(height: 6),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected ? Colors.white : const Color(0xFF94A3B8),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInputField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    bool obscure = false,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: obscure,
      validator: validator,
      style: const TextStyle(fontSize: 14, color: Colors.white),
      decoration: InputDecoration(
        prefixIcon: Icon(icon, color: const Color(0xFF6366F1), size: 18),
        labelText: label,
        labelStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFF475569), fontSize: 13),
        filled: true,
        fillColor: const Color(0xFF1E293B).withOpacity(0.4),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF334155), width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF334155), width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF4F46E5), width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.redAccent, width: 1),
        ),
      ),
    );
  }



  Widget _buildUpiAppLogo(String label, Color color, IconData icon) {
    return Column(
      children: [
        Container(
          height: 44,
          width: 44,
          decoration: BoxDecoration(
            color: color.withOpacity(0.15),
            shape: BoxShape.circle,
            border: Border.all(color: color, width: 1.5),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(height: 6),
        Text(
          label,
          style: const TextStyle(fontSize: 10, color: Color(0xFFCBD5E1), fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}
