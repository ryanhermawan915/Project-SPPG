import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/services/face_detection_service.dart';
import '../../routes/app_router.dart';

class FaceScanPage extends StatefulWidget {
  const FaceScanPage({super.key});

  @override
  State<FaceScanPage> createState() => _FaceScanPageState();
}

class _FaceScanPageState extends State<FaceScanPage> {
  CameraController? _cameraController;
  final FaceDetectionService _faceService = FaceDetectionService();
  bool _isCameraInitialized = false;
  bool _isScanning = false;
  bool _isScanned = false;
  String? _message;

  @override
  void initState() {
    super.initState();
    _initCamera();
  }

  Future<void> _initCamera() async {
    try {
      final cameras = await availableCameras();
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
      setState(() => _message = 'Gagal init kamera: $e');
    }
  }

  Future<void> _scan() async {
    if (_cameraController == null) return;
    setState(() {
      _isScanning = true;
      _message = null;
    });
    try {
      final photo = await _cameraController!.takePicture();
      final faces = await _faceService.detectFacesFromFile(photo.path);
      if (!mounted) return;
      if (faces.isEmpty) {
        setState(() {
          _message = 'Wajah tidak terdeteksi. Coba lagi.';
        });
      } else if (faces.length > 1) {
        setState(() {
          _message = 'Terdeteksi lebih dari 1 wajah. Pastikan hanya Anda.';
        });
      } else {
        setState(() => _isScanned = true);
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _message = 'Gagal scan: $e');
    } finally {
      if (mounted) setState(() => _isScanning = false);
    }
  }

  void _finish() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Registrasi wajah berhasil!'),
        backgroundColor: AppColors.success,
      ),
    );
    Navigator.pushNamedAndRemoveUntil(
      context,
      AppRoutes.home,
      (route) => false,
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
      appBar: AppBar(
        title: const Text('Registrasi Wajah'),
        automaticallyImplyLeading: false,
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Text(
                'Posisikan wajah Anda di dalam bingkai',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 14),
              ),
              const SizedBox(height: 16),
              Expanded(
                child: AspectRatio(
                  aspectRatio: 3 / 4,
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.black,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(
                        color:
                            _isScanned ? AppColors.success : AppColors.primary,
                        width: 3,
                      ),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: _isCameraInitialized && _cameraController != null
                        ? Stack(
                            fit: StackFit.expand,
                            children: [
                              CameraPreview(_cameraController!),
                              Center(
                                child: Container(
                                  width: 220,
                                  height: 280,
                                  decoration: BoxDecoration(
                                    border: Border.all(
                                      color:
                                          Colors.white.withValues(alpha: 0.6),
                                      width: 2,
                                    ),
                                    borderRadius: BorderRadius.circular(120),
                                  ),
                                ),
                              ),
                              if (_isScanned)
                                Container(
                                  color: Colors.green.withValues(alpha: 0.25),
                                  child: const Center(
                                    child: Icon(
                                      Icons.check_circle,
                                      color: Colors.white,
                                      size: 96,
                                    ),
                                  ),
                                ),
                            ],
                          )
                        : const Center(
                            child:
                                CircularProgressIndicator(color: Colors.white),
                          ),
                  ),
                ),
              ),
              if (_message != null) ...[
                const SizedBox(height: 12),
                Text(
                  _message!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: AppColors.danger, fontSize: 12),
                ),
              ],
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _isScanned
                      ? _finish
                      : (_isScanning || !_isCameraInitialized)
                          ? null
                          : _scan,
                  style: ElevatedButton.styleFrom(
                    backgroundColor:
                        _isScanned ? AppColors.success : AppColors.primary,
                  ),
                  child: _isScanning
                      ? const CircularProgressIndicator(color: Colors.white)
                      : Text(_isScanned ? 'Selesai & Masuk' : 'Mulai Scan'),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'Pastikan wajah terlihat jelas',
                style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
