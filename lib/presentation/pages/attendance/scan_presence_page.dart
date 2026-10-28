import 'dart:async';
import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/services/face_detection_service.dart';
import '../../../core/services/geofence_service.dart';

class ScanPresencePage extends StatefulWidget {
  const ScanPresencePage({super.key});

  @override
  State<ScanPresencePage> createState() => _ScanPresencePageState();
}

class _ScanPresencePageState extends State<ScanPresencePage> {
  CameraController? _cameraController;
  final FaceDetectionService _faceService = FaceDetectionService();
  bool _isCameraInitialized = false;
  bool _isScanning = false;
  bool _isProcessing = false;
  String _selectedMode = 'Berangkat';

  // Status
  bool? _locationOk;
  double? _distance;
  String? _locationMessage;
  bool _faceDetected = false;
  String? _faceMessage;

  @override
  void initState() {
    super.initState();
    _initCamera();
  }

  Future<void> _initCamera() async {
    try {
      final cameras = await availableCameras();
      if (cameras.isEmpty) {
        setState(() => _locationMessage = 'Kamera tidak tersedia');
        return;
      }
      // Pilih kamera depan
      final front = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );

      final controller = CameraController(
        front,
        ResolutionPreset.medium,
        enableAudio: false,
        imageFormatGroup: ImageFormatGroup.nv21,
      );

      await controller.initialize();
      if (!mounted) return;

      setState(() {
        _cameraController = controller;
        _isCameraInitialized = true;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _locationMessage = 'Gagal inisialisasi kamera: $e');
    }
  }

  Future<void> _checkLocation() async {
    setState(() => _isProcessing = true);
    final result = await GeofenceService.checkLocation();
    if (!mounted) return;
    setState(() {
      _isProcessing = false;
      _locationOk = result.isInside;
      _distance = result.distanceMeters;
      _locationMessage = result.errorMessage ??
          (result.isInside
              ? 'Anda berada dalam area sekolah (${GeofenceService.formatDistance(result.distanceMeters)} dari pusat)'
              : 'Anda berada di luar area sekolah (${GeofenceService.formatDistance(result.distanceMeters)} dari pusat)');
    });
  }

  Future<void> _startScan() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized) {
      return;
    }

    setState(() {
      _isScanning = true;
      _faceDetected = false;
      _faceMessage = null;
      _locationOk = null;
      _distance = null;
      _locationMessage = null;
    });

    try {
      // 1. Ambil foto dari kamera
      final XFile photo = await _cameraController!.takePicture();

      // 2. Deteksi wajah
      final faces = await _faceService.detectFacesFromFile(photo.path);
      if (!mounted) return;
      setState(() {
        _faceDetected = faces.isNotEmpty;
        _faceMessage = faces.isEmpty
            ? 'Wajah tidak terdeteksi. Pastikan wajah terlihat jelas.'
            : 'Wajah terdeteksi (${faces.length} wajah).';
      });

      // 3. Cek lokasi (geofencing)
      final loc = await GeofenceService.checkLocation();
      if (!mounted) return;
      setState(() {
        _locationOk = loc.isInside;
        _distance = loc.distanceMeters;
        _locationMessage = loc.errorMessage ??
            (loc.isInside
                ? 'Lokasi valid (${GeofenceService.formatDistance(loc.distanceMeters)})'
                : 'Di luar area (${GeofenceService.formatDistance(loc.distanceMeters)})');
      });

      // 4. Tentukan sukses
      if (faces.isNotEmpty && loc.isInside) {
        _showSuccessDialog();
      } else {
        _showFailureDialog(faces.isEmpty, loc.isInside);
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal scan: $e'),
          backgroundColor: AppColors.danger,
        ),
      );
    } finally {
      if (mounted) setState(() => _isScanning = false);
    }
  }

  void _showSuccessDialog() {
    final time = TimeOfDay.now().format(context);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.check_circle, color: AppColors.success),
            const SizedBox(width: 8),
            const Text('Presensi Berhasil'),
          ],
        ),
        content: Text(
          'Anda tercatat $_selectedMode pada $time.\n'
          'Wajah terdeteksi & lokasi valid.',
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pop(context);
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showFailureDialog(bool noFace, bool outside) {
    final reasons = <String>[];
    if (noFace) reasons.add('Wajah tidak terdeteksi');
    if (outside) reasons.add('Anda di luar area sekolah');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.error, color: AppColors.danger),
            const SizedBox(width: 8),
            const Text('Presensi Gagal'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Alasan:'),
            const SizedBox(height: 8),
            ...reasons.map((r) => Text('• $r')),
            const SizedBox(height: 12),
            const Text('Silakan coba lagi.'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _faceService.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Scan Wajah')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Text(
                'Posisikan wajah Anda di dalam bingkai',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 14),
              ),
              const SizedBox(height: 16),

              // Preview Kamera
              AspectRatio(
                aspectRatio: 3 / 4,
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.black,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: _faceDetected ? AppColors.success : AppColors.primary,
                      width: 3,
                    ),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: _isCameraInitialized && _cameraController != null
                      ? Stack(
                          fit: StackFit.expand,
                          children: [
                            CameraPreview(_cameraController!),
                            // Overlay bingkai wajah
                            Center(
                              child: Container(
                                width: 220,
                                height: 280,
                                decoration: BoxDecoration(
                                  border: Border.all(
                                    color: Colors.white.withValues(alpha: 0.6),
                                    width: 2,
                                  ),
                                  borderRadius: BorderRadius.circular(120),
                                ),
                              ),
                            ),
                            if (_isProcessing)
                              Container(
                                color: Colors.black54,
                                child: const Center(
                                  child: CircularProgressIndicator(
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                          ],
                        )
                      : const Center(
                          child: CircularProgressIndicator(color: Colors.white),
                        ),
                ),
              ),
              const SizedBox(height: 16),

              // Status wajah & lokasi
              if (_faceMessage != null)
                _StatusTile(
                  icon: _faceDetected ? Icons.face : Icons.face_retouching_off,
                  color: _faceDetected ? AppColors.success : AppColors.danger,
                  text: _faceMessage!,
                ),
              if (_locationMessage != null)
                _StatusTile(
                  icon: _locationOk == true
                      ? Icons.location_on
                      : Icons.location_off,
                  color: _locationOk == true
                      ? AppColors.success
                      : AppColors.danger,
                  text: _locationMessage!,
                ),

              const SizedBox(height: 16),

              // Tombol cek lokasi terpisah
              TextButton.icon(
                onPressed: _isProcessing ? null : _checkLocation,
                icon: const Icon(Icons.my_location, size: 18),
                label: const Text('Cek Lokasi Saja'),
              ),
              const SizedBox(height: 8),

              // Toggle Berangkat/Pulang
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: _ModeButton(
                        label: 'Berangkat',
                        selected: _selectedMode == 'Berangkat',
                        onTap: () =>
                            setState(() => _selectedMode = 'Berangkat'),
                      ),
                    ),
                    Expanded(
                      child: _ModeButton(
                        label: 'Pulang',
                        selected: _selectedMode == 'Pulang',
                        onTap: () => setState(() => _selectedMode = 'Pulang'),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: (_isScanning || !_isCameraInitialized)
                      ? null
                      : _startScan,
                  icon: _isScanning
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(Icons.camera_alt),
                  label: Text(_isScanning ? 'Memproses...' : 'Mulai Scan'),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'Pastikan wajah terlihat jelas dan Anda berada di area sekolah',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusTile extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String text;

  const _StatusTile({
    required this.icon,
    required this.color,
    required this.text,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: TextStyle(fontSize: 12, color: color),
            ),
          ),
        ],
      ),
    );
  }
}

class _ModeButton extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _ModeButton({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: selected ? AppColors.primary : Colors.transparent,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            color: selected ? Colors.white : AppColors.textSecondary,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }
}