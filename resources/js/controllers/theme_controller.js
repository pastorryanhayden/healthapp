import { Controller } from "@hotwired/stimulus"

// Connects to data-controller="theme"
export default class extends Controller {
    static values = {
        storageKey: { type: String, default: "theme" },
    }

    connect() {
        this.apply(this.storedTheme())
    }

    toggle() {
        const next = this.current() === "dark" ? "light" : "dark"

        this.apply(next)
        localStorage.setItem(this.storageKeyValue, next)
    }

    current() {
        return document.documentElement.getAttribute("data-theme") || "light"
    }

    storedTheme() {
        const stored = localStorage.getItem(this.storageKeyValue)

        if (stored === "light" || stored === "dark") {
            return stored
        }

        return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"
    }

    apply(theme) {
        document.documentElement.setAttribute("data-theme", theme)
    }
}
