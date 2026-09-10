# Healthapp

Personal calorie, walk, and Friday weigh-in tracker. One person, no accounts.

## Language

**Eating goal**:
The daily cap of tracked food calories. It is 2000. Coffee is never logged, so those ~200 calories sit outside the cap.
_Avoid_: 2200 goal, net calories, TDEE

**Food**:
A catalog entry: a normalized name and a calorie count. Created the first time that input is seen (from AI) and reused after that.
_Avoid_: meal, recipe, ingredient

**Food log**:
One line on a day's eating list. Stores the date, the Food, a calorie snapshot, and the original typed text.
_Avoid_: entry, meal log, diary item

**Walk**:
One segment of walking on a date, measured in miles. Several Walks on the same date add up.
_Avoid_: step, activity, workout

**Walk goal**:
Four miles in a calendar day. Pass when the day's Walks sum to at least 4.0.
_Avoid_: step goal, 10k steps

**Weigh-in**:
A body weight in pounds on a date, intended for Fridays. One Weigh-in per date; a later post replaces it.
_Avoid_: weigh in, weight log, check-in

**Weight goal**:
Two hundred five pounds. The line graph plots Weigh-ins toward this number.
_Avoid_: target weight, ideal weight

**Day**:
A calendar date in America/Chicago. Status is computed from that date's Food logs and Walks, not stored.
_Avoid_: session, streak day

**Eating pass**:
The Day has at least one Food log and calories eaten are at most 2000.
_Avoid_: on track, success, green eating

**Eating fail**:
The Day has no Food log, or calories eaten are over 2000. No log counts as a fail because the point of the app is to log.
_Avoid_: miss, skip

**Walking pass**:
The Day's Walks sum to at least 4.0 miles.

**Walking fail**:
The Day's Walks sum to less than 4.0 miles, including no Walks logged.
