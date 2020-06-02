import {ComponentNameToClassKey} from '@material-ui/core/styles/overrides';

declare module '@material-ui/core/styles/overrides' {
    interface ComponentNameToClassKey {
        MUIDataTable: any;
        MUIDataTableHeadCell: any;
        MUIDataTableSelectCell: any;
        MUIDataTableBodyCell: any;
        MUIDataTableToolbarSelect: any;
        MUIDataTableBodyRow: any;
        MUIDataTableToolbar: any;
        MuiDataTablePagination: any;
        MuiDataTableSortLabel: any;
    }
}