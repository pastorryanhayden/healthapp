import SwiftUI
import WidgetKit

private enum Ledger {
    static let ink = Color(red: 26 / 255, green: 23 / 255, blue: 20 / 255)
    static let cream = Color(red: 237 / 255, green: 230 / 255, blue: 220 / 255)
    static let muted = Color(red: 183 / 255, green: 169 / 255, blue: 154 / 255)
    static let fail = Color(red: 196 / 255, green: 92 / 255, blue: 74 / 255)
}

struct CaloriesEntry: TimelineEntry {
    let date: Date
    let remaining: Int
    let eaten: Int
    let goal: Int
    let loaded: Bool
}

struct CaloriesProvider: TimelineProvider {
    func placeholder(in context: Context) -> CaloriesEntry {
        sample
    }

    func getSnapshot(in context: Context, completion: @escaping (CaloriesEntry) -> Void) {
        if context.isPreview {
            completion(sample)
            return
        }
        Task {
            completion(await load())
        }
    }

    func getTimeline(in context: Context, completion: @escaping (Timeline<CaloriesEntry>) -> Void) {
        Task {
            let entry = await load()
            let next = Date().addingTimeInterval(15 * 60)
            completion(Timeline(entries: [entry], policy: .after(next)))
        }
    }

    private var sample: CaloriesEntry {
        CaloriesEntry(date: Date(), remaining: 1075, eaten: 925, goal: 2000, loaded: true)
    }

    private func load() async -> CaloriesEntry {
        guard let url = URL(string: "https://health.haydenindustries.com/api/today") else {
            return failed
        }
        var request = URLRequest(url: url, timeoutInterval: 8)
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        do {
            let (data, response) = try await URLSession.shared.data(for: request)
            guard let http = response as? HTTPURLResponse, (200 ..< 300).contains(http.statusCode) else {
                return failed
            }
            let today = try JSONDecoder().decode(TodayCalories.self, from: data)
            return CaloriesEntry(
                date: Date(),
                remaining: today.caloriesRemaining,
                eaten: today.caloriesEaten,
                goal: today.caloriesGoal,
                loaded: true
            )
        } catch {
            return failed
        }
    }

    private var failed: CaloriesEntry {
        CaloriesEntry(date: Date(), remaining: 0, eaten: 0, goal: 2000, loaded: false)
    }
}

private struct TodayCalories: Decodable {
    let caloriesRemaining: Int
    let caloriesEaten: Int
    let caloriesGoal: Int

    enum CodingKeys: String, CodingKey {
        case caloriesRemaining = "calories_remaining"
        case caloriesEaten = "calories_eaten"
        case caloriesGoal = "calories_goal"
    }
}

struct CaloriesWidgetView: View {
    @Environment(\.widgetFamily) private var family
    let entry: CaloriesEntry

    var body: some View {
        let remainingSize: CGFloat = family == .systemMedium ? 56 : 40
        let remainingColor = entry.loaded && entry.remaining < 0 ? Ledger.fail : Ledger.cream
        let caption = entry.loaded
            ? "left  ·  \(entry.eaten) / \(entry.goal)"
            : "can't load"

        VStack(alignment: .leading, spacing: 4) {
            if entry.loaded {
                Text("\(entry.remaining)")
                    .font(.system(size: remainingSize, weight: .semibold, design: .serif))
                    .monospacedDigit()
                    .foregroundStyle(remainingColor)
                    .minimumScaleFactor(0.4)
                    .lineLimit(1)
            } else {
                Text("—")
                    .font(.system(size: remainingSize, weight: .semibold, design: .serif))
                    .foregroundStyle(Ledger.cream)
            }
            Text(caption)
                .font(.system(size: family == .systemMedium ? 14 : 12, weight: .medium))
                .foregroundStyle(Ledger.muted)
                .monospacedDigit()
                .minimumScaleFactor(0.7)
                .lineLimit(2)
            Spacer(minLength: 0)
        }
        .frame(maxWidth: .infinity, maxHeight: .infinity, alignment: .topLeading)
        .padding(family == .systemMedium ? 18 : 14)
        .containerBackground(Ledger.ink, for: .widget)
    }
}

struct CaloriesWidget: Widget {
    var body: some WidgetConfiguration {
        StaticConfiguration(kind: "CaloriesWidget", provider: CaloriesProvider()) { entry in
            CaloriesWidgetView(entry: entry)
        }
        .configurationDisplayName("Calories")
        .description("Remaining calories for today.")
        .supportedFamilies([.systemSmall, .systemMedium])
        .contentMarginsDisabled()
    }
}

@main
struct CaloriesWidgetBundle: WidgetBundle {
    var body: some Widget {
        CaloriesWidget()
    }
}

#Preview(as: .systemSmall) {
    CaloriesWidget()
} timeline: {
    CaloriesEntry(date: .now, remaining: 1075, eaten: 925, goal: 2000, loaded: true)
}
