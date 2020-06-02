import {createMuiTheme, SimplePaletteColorOptions} from "@material-ui/core";
import {PaletteOptions} from "@material-ui/core/styles/createPalette";

const palette: PaletteOptions = {
    primary: {
        main: "#79aec8",
        contrastText: "#fff"
    },
    secondary: {
        main: "#4db5ab",
        contrastText: "#fff"
    },
    background: {
        default: "#fafafa"
    }
}

const theme = createMuiTheme({
    palette,
    overrides: {
        MUIDataTable: {
            paper: {
                boxShadow: "none",
            }
        },
        MUIDataTableToolbar: {
            root: {
                minHeight: '58px',
                backgroundColor: palette!.background!.default
            },
            icon: {
                color: (palette!.primary as SimplePaletteColorOptions).main,
                '&:hover, &:active, &.focus': {
                    color: '#055a52',
                },
            },
            iconActive: {
                color: '#055a52',
                '&:hover, &:active, &.focus': {
                    color: '#055a52',
                },
            },
        },
        MUIDataTableHeadCell: {
            fixedHeader: {
                paddingTop: 7,
                paddingBottom: 7,
                backgroundColor: (palette!.primary as SimplePaletteColorOptions).main,
                color: '#ffffff',
                '&[aria-sort]': {
                    backgroundColor: '#459ac4',
                }
            },
            sortActive: {
                color: '#fff'
            },
            sortAction: {
                alignItems: 'center'
            }
        }
    }
});

export default theme;