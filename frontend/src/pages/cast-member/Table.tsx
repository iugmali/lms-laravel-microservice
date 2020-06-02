// @flow
import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {useEffect, useState} from "react";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import castMemberHttp from "../../util/http/cast-member-http";

const CastMemberTypeMap = {
    1: 'Diretor',
    2: 'Ator'
}

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
    },
    {
        name: "type",
        label: "Cargo",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return CastMemberTypeMap[value];
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
        castMemberHttp.list().then(({data}) => setData(data.data));
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