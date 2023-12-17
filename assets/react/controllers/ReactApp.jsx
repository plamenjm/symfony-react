import React, { useMemo } from 'react';
import { createBrowserRouter, Outlet, RouterProvider } from 'react-router-dom';
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

export default function ReactApp({route, happyMessage}) {
    const router = useMemo(() => createBrowserRouter([
        {path: route, element: <AppMenu/>, children: [
                {path: '', element: <PageParams/>,},
                {path: '*', element: <PageParams/>,},
                {path: 'phpunit', element: <PagePhpunit/>,},
            ]},
    ]), [])

    return <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
}
