import React, {useMemo} from 'react';
import {createBrowserRouter, Outlet, RouterProvider} from 'react-router-dom';
import {Config, setConfig} from '/assets/Config';
import {NavMenu} from '/assets/react/components/NavMenu';
import {PageParams} from '/assets/react/PageParams';
import {PagePhpunit} from '/assets/react/PagePhpunit';
import {PageLiveTrades} from '/assets/react/PageLiveTrades';

function AppMenu() {
    return <>
        <header>
            <nav>
                <NavMenu to='' end replace>API</NavMenu>
                &nbsp;|&nbsp;
                <NavMenu to='phpunit' replace>phpunit</NavMenu>
                &nbsp;|&nbsp;
                <NavMenu to='trades' replace>Live Trades</NavMenu>
            </nav>
        </header>
        <hr/>
        <Outlet/>
    </>
}

export default function ReactApp({path, appJSConfig}) {
    const appConfigEnable = false // appJSConfig from PHP controller (only for React/SPA) // moved to app.js
    if (appConfigEnable) {
        const cfg = JSON.parse(appJSConfig)
        const fetchApi = Config.FetchApi.startsWith('http') || cfg.FetchApi
        Object.keys(cfg).forEach(key => Config[key]
          && (!Array.isArray(Config[key]) || !Config[key].length)
          && delete cfg[key])
        if (!fetchApi) cfg.FetchApi = window.location.origin + Config.FetchApi // backup
        setConfig(cfg)
    }


    //---

    const router = useMemo(() => createBrowserRouter([
        {path, element: <AppMenu/>, children: [
                {path: '', element: <PageParams/>,},
                {path: '*', element: <PageParams/>,},
                {path: 'phpunit', element: <PagePhpunit/>,},
                {path: 'trades', element: <PageLiveTrades/>,},
            ]},
    ]), [])

    return <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
}
