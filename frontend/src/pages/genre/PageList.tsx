// @flow 
import * as React from 'react';
import {Page} from "../../components/Page";
import {Box, Fab} from "@material-ui/core";
import AddIcon from "@material-ui/icons/Add";
import {Link} from "react-router-dom";
import Table from "./Table";


const PageList = () => {
    return (
        <Page title="Listagem de Generos">
            <Box dir={'rtl'}>
                <Fab title="Adicionar genero"
                    size="small"
                     component={Link}
                     to="/genres/create"
                >
                    <AddIcon />
                </Fab>
            </Box>
            <Box>
                <Table />
            </Box>
        </Page>
    );
};

export default PageList;