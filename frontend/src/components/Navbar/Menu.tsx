// @flow
import * as React from 'react';
import {IconButton, Menu as MuiMenu, MenuItem} from "@material-ui/core";
import MenuIcon from "@material-ui/icons/Menu";
import routes, {MyRouteProps} from "../../routes";
import {Link} from "react-router-dom";

const listRoutes = [
    'dashboard',
    'categories.list',
    'cast_members.list',
    'genres.list'
];
const menuRoutes = routes.filter(route => listRoutes.includes(route.name));

export const Menu = () => {
    const [anchorEl, setAnchorEl] = React.useState(null);
    const menuOpen = Boolean(anchorEl);
    const handleMenuOpen = (event: any) => setAnchorEl(event.currentTarget);
    const handleMenuClose = () => setAnchorEl(null);
    return (
        <>
            <IconButton
                edge="start"
                color="inherit"
                aria-label="open drawer"
                aria-controls="menu-appbar"
                aria-haspopup="true"
                onClick={handleMenuOpen}
            >
                <MenuIcon/>
            </IconButton>
            <MuiMenu
                open={menuOpen}
                id="menu-appbar"
                anchorEl={anchorEl}
                onClose={handleMenuClose}
                anchorOrigin={{vertical: 'bottom', horizontal: 'center'}}
                transformOrigin={{vertical: 'top', horizontal: 'center'}}
                getContentAnchorEl={null}
            >
                {
                    listRoutes.map(
                        (routeName, key) => {
                           const route = menuRoutes.find(route => route.name === routeName) as MyRouteProps;
                           return (
                               <MenuItem key={key} component={Link} to={route.path as string} onClick={handleMenuClose}>
                                   {route.label}
                               </MenuItem>
                           )
                        }
                    )
                }
            </MuiMenu>
        </>
    );
};