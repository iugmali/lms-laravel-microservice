// @flow
import * as React from 'react';
import {AppBar, Button, Theme, makeStyles, Toolbar, Typography} from "@material-ui/core";
import logo from '../../static/logo.png';
import {Menu} from "./Menu";

const useStyles = makeStyles( (theme: Theme) => ({
    toolbar: {
        backgroundColor: '#000000'
    },
    title: {
        flexGrow: 1,
        textAlign: 'center'
    },
    logo: {
        width: 100,
        [theme.breakpoints.up(`sm`)]: {
            width: 170
        }
    },
}));

export const Navbar: React.FC = () => {
    const classes = useStyles();
    return (
        <AppBar>
            <Toolbar className={classes.toolbar}>
                <Menu/>
                <Typography className={classes.title}>
                    <img src={logo} alt="Codeflix" className={classes.logo} />
                </Typography>
                <Button color="inherit">Login</Button>
            </Toolbar>
        </AppBar>
    );
};