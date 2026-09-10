import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class Ledger {
  static const ink = Color(0xFF1A1714);
  static const surface = Color(0xFF26221E);
  static const cream = Color(0xFFEDE6DC);
  static const muted = Color(0xFFB7A99A);
  static const line = Color(0xFF3A342E);
  static const pass = Color(0xFF7A9E68);
  static const fail = Color(0xFFC45C4A);
  static const ochre = Color(0xFFC4923A);

  static ThemeData dark() {
    final karla = GoogleFonts.karlaTextTheme(ThemeData.dark().textTheme);
    final sourceSerif = GoogleFonts.sourceSerif4TextTheme(karla);

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: ink,
      colorScheme: const ColorScheme.dark(
        surface: surface,
        primary: ochre,
        onPrimary: ink,
        secondary: cream,
        error: fail,
        onSurface: cream,
      ),
      textTheme: sourceSerif.copyWith(
        displayLarge: GoogleFonts.sourceSerif4(
          fontSize: 72,
          fontWeight: FontWeight.w600,
          height: 0.95,
          color: cream,
          fontFeatures: const [FontFeature.tabularFigures()],
        ),
        headlineMedium: GoogleFonts.sourceSerif4(
          fontSize: 28,
          fontWeight: FontWeight.w600,
          color: cream,
        ),
        titleMedium: GoogleFonts.karla(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: cream,
        ),
        bodyMedium: GoogleFonts.karla(
          fontSize: 15,
          height: 1.4,
          color: cream,
        ),
        bodySmall: GoogleFonts.karla(
          fontSize: 13,
          color: muted,
        ),
        labelLarge: GoogleFonts.karla(
          fontSize: 14,
          fontWeight: FontWeight.w600,
          color: cream,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        hintStyle: GoogleFonts.karla(color: muted),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(4),
          borderSide: const BorderSide(color: line),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(4),
          borderSide: const BorderSide(color: line),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(4),
          borderSide: const BorderSide(color: ochre, width: 1.4),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: ochre,
          foregroundColor: ink,
          minimumSize: const Size(48, 48),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
          textStyle: GoogleFonts.karla(fontWeight: FontWeight.w700, fontSize: 15),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: fail,
          textStyle: GoogleFonts.karla(fontWeight: FontWeight.w600),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: ink,
        indicatorColor: surface,
        labelTextStyle: WidgetStatePropertyAll(
          GoogleFonts.karla(fontSize: 12, fontWeight: FontWeight.w600),
        ),
      ),
      dividerColor: line,
    );
  }
}
