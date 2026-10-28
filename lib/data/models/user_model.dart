class UserModel {
  final String nip;
  final String nama;
  final String email;
  final String noHp;
  final String jenisKelamin;
  final String mataPelajaran;
  final String? fotoProfil;
  final bool isFaceRegistered;

  UserModel({
    required this.nip,
    required this.nama,
    required this.email,
    required this.noHp,
    required this.jenisKelamin,
    required this.mataPelajaran,
    this.fotoProfil,
    this.isFaceRegistered = false,
  });

  UserModel copyWith({
    String? nip,
    String? nama,
    String? email,
    String? noHp,
    String? jenisKelamin,
    String? mataPelajaran,
    String? fotoProfil,
    bool? isFaceRegistered,
  }) {
    return UserModel(
      nip: nip ?? this.nip,
      nama: nama ?? this.nama,
      email: email ?? this.email,
      noHp: noHp ?? this.noHp,
      jenisKelamin: jenisKelamin ?? this.jenisKelamin,
      mataPelajaran: mataPelajaran ?? this.mataPelajaran,
      fotoProfil: fotoProfil ?? this.fotoProfil,
      isFaceRegistered: isFaceRegistered ?? this.isFaceRegistered,
    );
  }
}