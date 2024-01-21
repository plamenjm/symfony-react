import {registerReactControllerComponents} from '@symfony/ux-react';
import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));


//---

import { Config, setConfig } from './Config';

(() => { // appJSConfig from PHP controller (JS global)
  const appConfigEnable = true
  if (!appConfigEnable) return
  try {
    //const appJSConfig = window['__appJSConfig'] // twig: <script>__appJSConfig = '{{ appJSConfig | serialize('json') | escape('js') }}'</script>
    const appJSConfig = document.querySelector('title').getAttribute('data-__appJSConfig') // twig: <title data-__appJSConfig="{{ appJSConfig | serialize('json') }}" ...
    if (appJSConfig === '""') return
    const cfg = JSON.parse(appJSConfig)
    if (!cfg) return

    const fetchApi = Config.FetchApi.startsWith('http') || cfg.FetchApi
    Object.keys(cfg).forEach(key => Config[key]
      && (!Array.isArray(Config[key]) || !Config[key].length)
      && delete cfg[key])
    if (!fetchApi) cfg.FetchApi = window.location.origin + Config.FetchApi // backup
    setConfig(cfg)
  } catch (ex) {
    console.log(ex)
  }
})()
