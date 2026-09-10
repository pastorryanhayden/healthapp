import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'pages.dart';
import 'store.dart';
import 'theme.dart';
import 'widgets.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  final store = HealthStore()..refresh();
  runApp(HealthApp(store: store));
}

class HealthApp extends StatelessWidget {
  const HealthApp({super.key, required this.store});

  final HealthStore store;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Healthapp',
      debugShowCheckedModeBanner: false,
      theme: Ledger.dark(),
      home: ListenableBuilder(
        listenable: store,
        builder: (context, _) => AdaptiveShell(store: store),
      ),
    );
  }
}

class AdaptiveShell extends StatefulWidget {
  const AdaptiveShell({super.key, required this.store});

  final HealthStore store;

  @override
  State<AdaptiveShell> createState() => _AdaptiveShellState();
}

class _AdaptiveShellState extends State<AdaptiveShell> {
  int index = 0;

  @override
  Widget build(BuildContext context) {
    final wide = MediaQuery.sizeOf(context).width >= 980;
    final today = TodayPage(store: widget.store);
    final calendar = CalendarPage(store: widget.store);
    final weight = WeightPage(store: widget.store);

    final keyboardOpen = MediaQuery.viewInsetsOf(context).bottom > 0;

    if (wide) {
      return Scaffold(
        body: GestureDetector(
          onTap: dismissKeyboard,
          behavior: HitTestBehavior.translucent,
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    'Healthapp',
                    style: GoogleFonts.karla(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.4,
                      color: Ledger.muted,
                    ),
                  ),
                ),
              ),
              Expanded(
                flex: 3,
                child: Row(
                  children: [
                    Expanded(flex: 5, child: today),
                    const VerticalDivider(width: 1, color: Ledger.line),
                    Expanded(flex: 4, child: calendar),
                  ],
                ),
              ),
              const Divider(height: 1, color: Ledger.line),
              Expanded(flex: 2, child: weight),
            ],
          ),
        ),
        bottomNavigationBar: keyboardOpen ? const KeyboardDoneBar() : null,
      );
    }

    final pages = [today, calendar, weight];
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Ledger.ink,
        surfaceTintColor: Colors.transparent,
        title: Text(
          'Healthapp',
          style: GoogleFonts.karla(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: Ledger.cream,
          ),
        ),
      ),
      body: GestureDetector(
        onTap: dismissKeyboard,
        behavior: HitTestBehavior.translucent,
        child: pages[index],
      ),
      bottomNavigationBar: keyboardOpen
          ? const KeyboardDoneBar()
          : NavigationBar(
              selectedIndex: index,
              onDestinationSelected: (value) {
                dismissKeyboard();
                setState(() => index = value);
              },
              destinations: const [
                NavigationDestination(icon: Icon(Icons.today_outlined), label: 'Today'),
                NavigationDestination(icon: Icon(Icons.calendar_month_outlined), label: 'Month'),
                NavigationDestination(icon: Icon(Icons.monitor_weight_outlined), label: 'Weight'),
              ],
            ),
    );
  }
}
