import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import 'api.dart';
import 'store.dart';
import 'theme.dart';
import 'widgets.dart';

class TodayPage extends StatefulWidget {
  const TodayPage({super.key, required this.store});

  final HealthStore store;

  @override
  State<TodayPage> createState() => _TodayPageState();
}

class _TodayPageState extends State<TodayPage> {
  final _food = TextEditingController();
  final _walk = TextEditingController();
  final _foodFocus = FocusNode();
  String? _foodError;

  HealthStore get store => widget.store;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (MediaQuery.sizeOf(context).width >= 900) {
        _foodFocus.requestFocus();
      }
    });
  }

  @override
  void dispose() {
    _food.dispose();
    _walk.dispose();
    _foodFocus.dispose();
    super.dispose();
  }

  Future<void> _submitFood() async {
    final text = _food.text.trim();
    if (text.isEmpty || store.estimating) return;
    setState(() => _foodError = null);
    try {
      await store.logFood(text);
      _food.clear();
      dismissKeyboard();
    } on EstimateFailedException {
      setState(() => _foodError = 'Could not estimate calories.');
    } catch (e) {
      setState(() => _foodError = e.toString());
    }
  }

  Future<void> _submitWalk() async {
    final miles = double.tryParse(_walk.text.trim());
    if (miles == null || miles <= 0) return;
    await store.logWalk(miles);
    _walk.clear();
    dismissKeyboard();
  }

  @override
  Widget build(BuildContext context) {
    final day = store.today;
    if (store.loading && day == null) {
      return const Center(child: CircularProgressIndicator(color: Ledger.ochre));
    }
    if (day == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(store.error ?? 'Could not load today.'),
        ),
      );
    }

    return ListView(
      keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 40),
      children: [
        Text(day.date, style: Theme.of(context).textTheme.bodySmall),
        const SizedBox(height: 4),
        Text(
          '${day.caloriesRemaining}',
          style: Theme.of(context).textTheme.displayLarge,
        ),
        Text(
          'left  ·  ${day.caloriesEaten} / ${day.caloriesGoal} calories',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            PassChip(label: 'Eating', pass: day.eatingPass),
            const SizedBox(width: 16),
            PassChip(label: 'Walking', pass: day.walkingPass),
          ],
        ),
        const SizedBox(height: 20),
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _food,
                focusNode: _foodFocus,
                enabled: !store.estimating,
                textInputAction: TextInputAction.done,
                onTapOutside: (_) => dismissKeyboard(),
                onSubmitted: (_) => _submitFood(),
                decoration: const InputDecoration(
                  hintText: 'What did you eat?',
                ),
              ),
            ),
            const SizedBox(width: 8),
            FilledButton(
              onPressed: store.estimating ? null : _submitFood,
              child: Text(store.estimating ? '…' : 'Log food'),
            ),
          ],
        ),
        if (store.estimating)
          const Padding(
            padding: EdgeInsets.only(top: 8),
            child: LinearProgressIndicator(
              color: Ledger.ochre,
              backgroundColor: Ledger.line,
            ),
          ),
        if (_foodError != null)
          Padding(
            padding: const EdgeInsets.only(top: 8),
            child: Text(_foodError!, style: const TextStyle(color: Ledger.fail)),
          ),
        const SizedBox(height: 16),
        if (day.foodLogs.isEmpty)
          const Text('Nothing logged yet — that’s a fail until you do.')
        else
          ...day.foodLogs.map((log) => _FoodRow(store: store, log: log)),
        const SizedBox(height: 28),
        Text('Walks', style: Theme.of(context).textTheme.headlineMedium),
        Text(
          '${day.milesWalked.toStringAsFixed(1)} / ${day.milesGoal.toStringAsFixed(0)} miles',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _walk,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                textInputAction: TextInputAction.done,
                onTapOutside: (_) => dismissKeyboard(),
                onSubmitted: (_) => _submitWalk(),
                decoration: const InputDecoration(hintText: 'Miles'),
              ),
            ),
            const SizedBox(width: 8),
            FilledButton(
              onPressed: _submitWalk,
              child: const Text('Log walk'),
            ),
          ],
        ),
        const SizedBox(height: 12),
        if (day.walks.isEmpty)
          const Text('No walks yet.')
        else
          ...day.walks.map((walk) => _WalkRow(store: store, walk: walk)),
      ],
    );
  }
}

class _FoodRow extends StatefulWidget {
  const _FoodRow({required this.store, required this.log});

  final HealthStore store;
  final FoodLog log;

  @override
  State<_FoodRow> createState() => _FoodRowState();
}

class _FoodRowState extends State<_FoodRow> {
  late final TextEditingController _name;
  late final TextEditingController _calories;

  @override
  void initState() {
    super.initState();
    _name = TextEditingController(text: widget.log.name);
    _calories = TextEditingController(text: '${widget.log.calories}');
  }

  @override
  void didUpdateWidget(covariant _FoodRow oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.log.id != widget.log.id ||
        oldWidget.log.calories != widget.log.calories) {
      _name.text = widget.log.name;
      _calories.text = '${widget.log.calories}';
    }
  }

  @override
  void dispose() {
    _name.dispose();
    _calories.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final calories = int.tryParse(_calories.text.trim());
    if (calories == null || calories < 1 || _name.text.trim().isEmpty) return;
    await widget.store.updateFood(
      id: widget.log.id,
      name: _name.text.trim(),
      calories: calories,
    );
  }

  Future<void> _delete() async {
    final ok = await confirmDelete(
      context,
      title: 'Delete ${widget.log.name}?',
    );
    if (!ok || !mounted) return;
    await widget.store.deleteFood(widget.log.id);
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _name,
              textInputAction: TextInputAction.next,
              onTapOutside: (_) => dismissKeyboard(),
              decoration: const InputDecoration(isDense: true),
            ),
          ),
          const SizedBox(width: 8),
          SizedBox(
            width: 72,
            child: TextField(
              controller: _calories,
              keyboardType: TextInputType.number,
              textInputAction: TextInputAction.done,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              onTapOutside: (_) => dismissKeyboard(),
              onSubmitted: (_) => _save(),
              decoration: const InputDecoration(isDense: true),
              style: GoogleFonts.karla(
                fontFeatures: const [FontFeature.tabularFigures()],
              ),
            ),
          ),
          TextButton(onPressed: _save, child: const Text('Save', style: TextStyle(color: Ledger.cream))),
          TextButton(onPressed: _delete, child: const Text('Delete')),
        ],
      ),
    );
  }
}

class _WalkRow extends StatefulWidget {
  const _WalkRow({required this.store, required this.walk});

  final HealthStore store;
  final WalkLog walk;

  @override
  State<_WalkRow> createState() => _WalkRowState();
}

class _WalkRowState extends State<_WalkRow> {
  late final TextEditingController _miles;

  @override
  void initState() {
    super.initState();
    _miles = TextEditingController(text: widget.walk.miles.toString());
  }

  @override
  void dispose() {
    _miles.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final miles = double.tryParse(_miles.text.trim());
    if (miles == null || miles <= 0) return;
    await widget.store.updateWalk(id: widget.walk.id, miles: miles);
  }

  Future<void> _delete() async {
    final miles = widget.walk.miles.toStringAsFixed(
      widget.walk.miles.truncateToDouble() == widget.walk.miles ? 0 : 1,
    );
    final ok = await confirmDelete(context, title: 'Delete $miles mi walk?');
    if (!ok || !mounted) return;
    await widget.store.deleteWalk(widget.walk.id);
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _miles,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              textInputAction: TextInputAction.done,
              onTapOutside: (_) => dismissKeyboard(),
              onSubmitted: (_) => _save(),
              decoration: const InputDecoration(isDense: true, suffixText: 'mi'),
            ),
          ),
          TextButton(onPressed: _save, child: const Text('Save', style: TextStyle(color: Ledger.cream))),
          TextButton(onPressed: _delete, child: const Text('Delete')),
        ],
      ),
    );
  }
}

class CalendarPage extends StatelessWidget {
  const CalendarPage({super.key, required this.store});

  final HealthStore store;

  @override
  Widget build(BuildContext context) {
    final month = store.calendar;
    if (month == null) {
      return const Center(child: CircularProgressIndicator(color: Ledger.ochre));
    }
    final first = DateTime.parse(month.days.first.date);
    final pad = first.weekday % 7;
    const labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(month.month, style: Theme.of(context).textTheme.headlineMedium),
          const SizedBox(height: 4),
          Text('Eat then walk. Red until you log.', style: Theme.of(context).textTheme.bodySmall),
          const SizedBox(height: 16),
          Row(
            children: labels
                .map(
                  (label) => Expanded(
                    child: Text(
                      label,
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ),
                )
                .toList(),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: GridView.builder(
              itemCount: pad + month.days.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 7,
                mainAxisSpacing: 6,
                crossAxisSpacing: 6,
                childAspectRatio: 0.9,
              ),
              itemBuilder: (context, index) {
                if (index < pad) return const SizedBox.shrink();
                final day = month.days[index - pad];
                return DecoratedBox(
                  decoration: BoxDecoration(
                    border: Border.all(color: Ledger.line),
                    borderRadius: BorderRadius.circular(4),
                    color: Ledger.surface,
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(6),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${day.dayNumber}',
                          style: GoogleFonts.karla(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: Ledger.cream,
                          ),
                        ),
                        const Spacer(),
                        DayMarks(day: day, compact: true),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class WeightPage extends StatefulWidget {
  const WeightPage({super.key, required this.store});

  final HealthStore store;

  @override
  State<WeightPage> createState() => _WeightPageState();
}

class _WeightPageState extends State<WeightPage> {
  final _pounds = TextEditingController();

  @override
  void dispose() {
    _pounds.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final pounds = double.tryParse(_pounds.text.trim());
    if (pounds == null || pounds <= 0) return;
    await widget.store.saveWeighIn(pounds);
    _pounds.clear();
    dismissKeyboard();
  }

  @override
  Widget build(BuildContext context) {
    final history = widget.store.weighIns;
    if (history == null) {
      return const Center(child: CircularProgressIndicator(color: Ledger.ochre));
    }

    String remainingCopy;
    if (history.remainingPounds == null) {
      remainingCopy = 'No weigh-in yet.';
    } else if (history.remainingPounds! > 0) {
      remainingCopy = '${history.remainingPounds!.toStringAsFixed(1)} lb to go';
    } else if (history.remainingPounds! < 0) {
      remainingCopy =
          '${history.remainingPounds!.abs().toStringAsFixed(1)} lb under goal';
    } else {
      remainingCopy = 'at goal';
    }

    return ListView(
      keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 40),
      children: [
        Text('Weigh-in', style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 4),
        Text(
          'Goal ${history.goalPounds.toStringAsFixed(0)} lb  ·  $remainingCopy',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        if (history.data.isNotEmpty)
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              'Latest: ${history.data.first.pounds} lb on ${history.data.first.date}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _pounds,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                textInputAction: TextInputAction.done,
                onTapOutside: (_) => dismissKeyboard(),
                decoration: const InputDecoration(hintText: 'Pounds'),
                onSubmitted: (_) => _save(),
              ),
            ),
            const SizedBox(width: 8),
            FilledButton(onPressed: _save, child: const Text('Save')),
          ],
        ),
        const SizedBox(height: 24),
        WeightChart(history: history),
      ],
    );
  }
}
