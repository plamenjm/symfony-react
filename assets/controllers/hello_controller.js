import { Controller } from '@hotwired/stimulus';
import packageJson from '/package.json';
 
/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller='hello' attribute will cause
 * this controller to be executed. The name 'hello' comes from the filename:
 * hello_controller.js -> 'hello'
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets = [ 'error', 'content' ]

    connect() {
        try {
            const dep = packageJson.dependencies, dev = packageJson.devDependencies
            this.contentTarget.textContent =
              "dependencies:\n" + Object.keys(dep).map(key => key + ' => ' + dep[key]).join("\n")
              + "\n\n" +
              "devDependencies:\n" + Object.keys(dev).map(key => key + ' => ' + dev[key]).join("\n")
        } catch (ex) { // to-do: stimulus handle error
            this.errorTarget.textContent = ex.message
        }
    }
}
