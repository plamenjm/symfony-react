import React, {CSSProperties} from 'react';
import {NavLink, NavLinkProps} from 'react-router-dom';

const StyleNavLinkInactive: CSSProperties = {fontWeight: 'bold', color: 'unset', textDecoration: 'none', pointerEvents: 'none'}

export function NavMenu({children, ...props}: NavLinkProps) {
    return (
        <NavLink {...props} style={({isActive}) => isActive ? StyleNavLinkInactive : undefined}>
            {children}
        </NavLink>
    )
}
