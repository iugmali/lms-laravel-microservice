// @flow
import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {useEffect, useState} from "react";
import {Chip} from "@material-ui/core";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import genreHttp from "../../util/http/genre-http";

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
    },
    {
        name: "is_active",
        label: "Ativo?",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value ? <Chip label="Sim" color="primary"></Chip> : <Chip label="Não" color="secondary"></Chip>;
            }
        }
    },
    {
        name: "categories",
        label: "Categorias",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value.map(value => value.name).join(', ');
            }
        }
    },
    {
        name: "created_at",
        label: "Criado em:",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>;
            }
        }

    }
];

const Table = () => {
    const [data, setData] = useState([]);

    useEffect( () => {
        genreHttp.list().then(({data}) => setData(data.data));
    }, []);

    return (
        <div>
            <MUIDataTable
                title=""
                columns={columnsDefinition}
                data={data}
            >
            </MUIDataTable>
        </div>
    );
};

export default Table;