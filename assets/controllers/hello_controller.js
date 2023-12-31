import { Controller } from '@hotwired/stimulus';
import composerJson from '/composer.json';
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
    static targets = ['error', 'composer', 'package']

    connect() {
        try {
            const composerDep = composerJson['require'], composerDev = composerJson['require-dev']
            const packageDep = packageJson['dependencies'], packageDev = packageJson['devDependencies']

            const composerDepFilter = key => ['api-platform', 'doctrine', 'process', 'stimulus', 'react', 'validator']
              .find(val => key.indexOf(val) >= 0)
            const composerDevFilter = key => ['phpunit']
              .find(val => key.indexOf(val) >= 0)
            const packageDepFilter = key => ![]
              .find(val => key.indexOf(val) >= 0)
            const packageDevFilter = key => !['babel', 'webpack', 'regenerator']
              .find(val => key.indexOf(val) >= 0)

            this.composerTarget.innerHTML =
              '<b>composer.require:</b><br/>' + Object.keys(composerDep).filter(composerDepFilter)
                .map(key => key + ' => ' + composerDep[key]).join('<br/>')
              + '<br/><br/>' +
              '<b>composer.require-dev:</b><br/>' + Object.keys(composerDev).filter(composerDevFilter)
                .map(key => key + ' => ' + composerDev[key]).join('<br/>')

            this.packageTarget.innerHTML =
              '<b>package.dependencies:</b><br/>' + Object.keys(packageDep).filter(packageDepFilter)
                .map(key => key + ' => ' + packageDep[key]).join('<br/>')
              + '<br/><br/>' +
              '<b>package.devDependencies:</b><br/>' + Object.keys(packageDev).filter(packageDevFilter)
                .map(key => key + ' => ' + packageDev[key]).join('<br/>')

        } catch (ex) { // to-do: stimulus handle error
            this.errorTarget.textContent = ex.message
        }
    }
}
