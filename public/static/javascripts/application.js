import { Application, Controller } from "./stimulus.3.0.1.js"
window.Stimulus = Application.start()

Stimulus.register("confirmation", class extends Controller {
    static values = {
        message: String,
    }

    confirm (event) {
        if (!confirm(this.messageValue)) {
            event.preventDefault();
        }
    }
})
