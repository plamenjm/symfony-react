import React, { useMemo } from 'react';
import { createBrowserRouter, Outlet, RouterProvider } from 'react-router-dom';
import { Config } from '/assets/Utils';
import { NavMenu } from '/assets/react/components/NavMenu';
import { PageParams } from '/assets/react/PageParams';
import { PagePhpunit } from '/assets/react/PagePhpunit';

function AppMenu() {
    return <>
        <header>
            <nav>
                <NavMenu to='' end replace>API</NavMenu>
                &nbsp;|&nbsp;
                <NavMenu to='phpunit' replace>phpunit</NavMenu>
            </nav>
        </header>
        <hr/>
        <Outlet/>
    </>
}

export default function ReactApp({path, urlApi}) {
    if (!Config.FetchApi.startsWith('http')) Config.FetchApi = urlApi
      ? urlApi + '/'
      : window.location.origin + Config.FetchApi

    const router = useMemo(() => createBrowserRouter([
        {path, element: <AppMenu/>, children: [
                {path: '', element: <PageParams/>,},
                {path: '*', element: <PageParams/>,},
                {path: 'phpunit', element: <PagePhpunit/>,},
            ]},
    ]), [])

    return <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
}
