import 'dart:ui' as ui;

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import 'api.dart';
import 'theme.dart';

void dismissKeyboard() {
  FocusManager.instance.primaryFocus?.unfocus();
}

/// iOS number pads have no return key. Sit a Done bar above the keyboard.
class KeyboardDoneBar extends StatelessWidget {
  const KeyboardDoneBar({super.key});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Ledger.surface,
      child: SizedBox(
        height: 44,
        child: Row(
          children: [
            TextButton(
              onPressed: dismissKeyboard,
              child: const Text('Cancel', style: TextStyle(color: Ledger.cream)),
            ),
            const Spacer(),
            TextButton(
              onPressed: dismissKeyboard,
              child: const Text(
                'Done',
                style: TextStyle(
                  color: Ledger.ochre,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

Future<bool> confirmDelete(
  BuildContext context, {
  required String title,
}) async {
  final ok = await showCupertinoDialog<bool>(
    context: context,
    builder: (context) => CupertinoAlertDialog(
      title: Text(title),
      content: const Text('This cannot be undone.'),
      actions: [
        CupertinoDialogAction(
          onPressed: () => Navigator.pop(context, false),
          child: const Text('Keep'),
        ),
        CupertinoDialogAction(
          isDestructiveAction: true,
          onPressed: () => Navigator.pop(context, true),
          child: const Text('Delete'),
        ),
      ],
    ),
  );
  return ok == true;
}

class PassChip extends StatelessWidget {
  const PassChip({super.key, required this.label, required this.pass});

  final String label;
  final bool pass;

  @override
  Widget build(BuildContext context) {
    final color = pass ? Ledger.pass : Ledger.fail;
    return Text(
      '$label ${pass ? 'pass' : 'fail'}',
      style: GoogleFonts.karla(
        fontSize: 13,
        fontWeight: FontWeight.w700,
        color: color,
        letterSpacing: 0.2,
      ),
    );
  }
}

class DayMarks extends StatelessWidget {
  const DayMarks({super.key, required this.day, this.compact = false});

  final CalendarDay day;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final size = compact ? 13.0 : 16.0;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          day.eating == 'pass' ? '✓' : '✕',
          style: TextStyle(
            color: day.eating == 'pass' ? Ledger.pass : Ledger.fail,
            fontSize: size,
            height: 1,
          ),
        ),
        const SizedBox(width: 4),
        Text(
          day.walking == 'pass' ? '✓' : '✕',
          style: TextStyle(
            color: day.walking == 'pass' ? Ledger.pass : Ledger.fail,
            fontSize: size,
            height: 1,
          ),
        ),
        if (day.weighIn) ...[
          const SizedBox(width: 4),
          Text('·', style: TextStyle(color: Ledger.muted, fontSize: size)),
        ],
      ],
    );
  }
}

class WeightChart extends StatelessWidget {
  const WeightChart({super.key, required this.history});

  final WeighInHistory history;

  @override
  Widget build(BuildContext context) {
    if (history.series.isEmpty) {
      return const Align(
        alignment: Alignment.centerLeft,
        child: Text('Log weigh-ins to see the trend to 205 lb.'),
      );
    }

    return SizedBox(
      height: 240,
      width: double.infinity,
      child: CustomPaint(
        painter: _WeightPainter(history),
      ),
    );
  }
}

class _WeightPainter extends CustomPainter {
  _WeightPainter(this.history);

  final WeighInHistory history;

  @override
  void paint(Canvas canvas, Size size) {
    final points = history.series;
    const padL = 40.0, padR = 48.0, padT = 16.0, padB = 28.0;
    final innerW = size.width - padL - padR;
    final innerH = size.height - padT - padB;
    final pounds = points.map((p) => p.pounds).toList();
    final goal = history.goalPounds;
    final min = ([goal, ...pounds].reduce((a, b) => a < b ? a : b)) - 2;
    final max = ([goal, ...pounds].reduce((a, b) => a > b ? a : b)) + 2;
    final span = (max - min).clamp(1, 1000);

    double xFor(int i) {
      if (points.length == 1) return padL + innerW / 2;
      return padL + (i / (points.length - 1)) * innerW;
    }

    double yFor(double v) => padT + ((max - v) / span) * innerH;

    final axis = TextPainter(textDirection: ui.TextDirection.ltr);
    void label(String text, Offset offset, {TextAlign align = TextAlign.left}) {
      axis
        ..text = TextSpan(
          text: text,
          style: GoogleFonts.karla(color: Ledger.muted, fontSize: 11),
        )
        ..textAlign = align
        ..layout();
      axis.paint(canvas, offset);
    }

    final goalY = yFor(goal);
    canvas.drawLine(
      Offset(padL, goalY),
      Offset(size.width - padR, goalY),
      Paint()
        ..color = Ledger.cream.withValues(alpha: 0.35)
        ..strokeWidth = 1,
    );
    label('205 lb', Offset(size.width - padR - 36, goalY - 16));

    label(max.toStringAsFixed(0), Offset(0, padT - 4));
    label(min.toStringAsFixed(0), Offset(0, size.height - padB - 8));

    if (points.length >= 2) {
      final path = Path()..moveTo(xFor(0), yFor(points.first.pounds));
      for (var i = 1; i < points.length; i++) {
        path.lineTo(xFor(i), yFor(points[i].pounds));
      }
      canvas.drawPath(
        path,
        Paint()
          ..color = Ledger.ochre
          ..strokeWidth = 2.4
          ..style = PaintingStyle.stroke
          ..strokeCap = StrokeCap.round
          ..strokeJoin = StrokeJoin.round,
      );
    }

    for (var i = 0; i < points.length; i++) {
      canvas.drawCircle(
        Offset(xFor(i), yFor(points[i].pounds)),
        4,
        Paint()..color = Ledger.ochre,
      );
      final stamp = DateFormat('M/d').format(DateTime.parse(points[i].date));
      label(stamp, Offset(xFor(i) - 12, size.height - 18));
    }
  }

  @override
  bool shouldRepaint(covariant _WeightPainter oldDelegate) =>
      oldDelegate.history != history;
}
