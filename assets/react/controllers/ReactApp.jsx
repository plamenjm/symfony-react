import React, {useMemo} from 'react';
import {createBrowserRouter, Outlet, RouterProvider} from 'react-router-dom';
import {Config} from '/assets/Config';
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

export default function ReactApp({path, urlApi}) {
    if (!Config.FetchApi.value.startsWith('http')) Config.FetchApi.value = urlApi
      ? urlApi + '/'
      : window.location.origin + Config.FetchApi.value

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
