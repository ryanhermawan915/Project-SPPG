import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../data/models/attendance_model.dart';

class StatusBadge extends StatelessWidget {
  final AttendanceStatus status;

  const StatusBadge({super.key, required this.status});

  Color get _color {
    switch (status) {
      case AttendanceStatus.hadir:
        return AppColors.success;
      case AttendanceStatus.terlambat:
        return AppColors.warning;
      case AttendanceStatus.izin:
        return AppColors.info;
      case AttendanceStatus.sakit:
        return const Color(0xFF9C27B0);
      case AttendanceStatus.alpha:
        return AppColors.danger;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: _color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _color.withValues(alpha: 0.4)),
      ),
      child: Text(
        status.label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: _color,
        ),
      ),
    );
  }
}