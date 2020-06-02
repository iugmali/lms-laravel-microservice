// @flow
import * as React from 'react';
import {Form} from "./Form";
import {Page} from "../../components/Page";

export const PageForm = () => {
    return (
        <Page title={'Criar Categoria'}>
            <Form />
        </Page>
    );
};

export default PageForm;