import { Controller } from "@hotwired/stimulus"

// Connects to data-controller="weight-chart"
export default class extends Controller {
    static targets = ["canvas"]

    static values = {
        points: { type: Array, default: [] },
        goal: { type: Number, default: 205 },
    }

    connect() {
        this.render()
    }

    pointsValueChanged() {
        this.render()
    }

    goalValueChanged() {
        this.render()
    }

    render() {
        if (!this.hasCanvasTarget) {
            return
        }

        const points = this.pointsValue.filter((point) => Number.isFinite(Number(point.pounds)))
        const width = 640
        const height = 280
        const pad = { top: 20, right: 16, bottom: 36, left: 48 }
        const innerWidth = width - pad.left - pad.right
        const innerHeight = height - pad.top - pad.bottom
        const goal = this.goalValue
        const pounds = points.map((point) => Number(point.pounds))
        const min = Math.min(goal, ...pounds, goal) - 2
        const max = Math.max(goal, ...pounds, goal) + 2
        const span = max - min || 1

        const xFor = (index) => {
            if (points.length <= 1) {
                return pad.left + innerWidth / 2
            }

            return pad.left + (index / (points.length - 1)) * innerWidth
        }

        const yFor = (value) => pad.top + ((max - value) / span) * innerHeight

        const styles = getComputedStyle(this.element)
        const ink = styles.getPropertyValue("--color-base-content").trim() || "currentColor"
        const primary = styles.getPropertyValue("--color-primary").trim() || ink
        const muted = styles.getPropertyValue("--color-base-content").trim() || ink

        const goalY = yFor(goal)
        const line = points
            .map((point, index) => `${index === 0 ? "M" : "L"} ${xFor(index).toFixed(1)} ${yFor(Number(point.pounds)).toFixed(1)}`)
            .join(" ")

        const yTicks = [max, (max + min) / 2, min]
            .sort((a, b) => b - a)
            .filter((tick, index, ticks) => index === 0 || Math.abs(ticks[index - 1] - tick) > span * 0.12)

        const xLabels = points.map((point, index) => {
            const date = String(point.date ?? "")
            const label = date.slice(5).replace("-", "/")

            return `<text x="${xFor(index).toFixed(1)}" y="${height - 12}" text-anchor="middle" font-size="11" fill="${muted}" fill-opacity="0.7">${this.escape(label)}</text>`
        })

        const dots = points.map((point, index) => {
            return `<circle cx="${xFor(index).toFixed(1)}" cy="${yFor(Number(point.pounds)).toFixed(1)}" r="4" fill="${primary}" />`
        })

        this.canvasTarget.setAttribute("viewBox", `0 0 ${width} ${height}`)
        this.canvasTarget.innerHTML = `
            <line x1="${pad.left}" y1="${goalY.toFixed(1)}" x2="${width - pad.right}" y2="${goalY.toFixed(1)}" stroke="${ink}" stroke-opacity="0.35" stroke-dasharray="6 4" />
            <text x="${width - pad.right}" y="${(goalY - 6).toFixed(1)}" text-anchor="end" font-size="11" fill="${ink}" fill-opacity="0.7">205 lb</text>
            ${yTicks.map((tick) => {
                const y = yFor(tick)
                return `<text x="${pad.left - 8}" y="${y + 3}" text-anchor="end" font-size="11" fill="${muted}" fill-opacity="0.7">${tick.toFixed(0)}</text>`
            }).join("")}
            ${line ? `<path d="${line}" fill="none" stroke="${primary}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />` : ""}
            ${dots.join("")}
            ${xLabels.join("")}
        `
    }

    escape(value) {
        return value
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
    }
}
