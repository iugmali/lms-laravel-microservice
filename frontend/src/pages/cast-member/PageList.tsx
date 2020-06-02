// @flow 
import * as React from 'react';
import {Page} from "../../components/Page";
import {Box, Fab} from "@material-ui/core";
import AddIcon from "@material-ui/icons/Add";
import {Link} from "react-router-dom";
import Table from "./Table";


const PageList = () => {
    return (
        <Page title="Cast Members">
            <Box dir={'rtl'}>
                <Fab title="Adicionar Cast Member"
                    size="small"
                     component={Link}
                     to="/cast_members/create"
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