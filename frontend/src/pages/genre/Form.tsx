// @flow
import * as React from 'react';
import {useEffect, useState} from "react";
import {Box, Button, makeStyles, ButtonProps, MenuItem, TextField, Theme} from "@material-ui/core";
import {DeepPartial, useForm} from "react-hook-form";
import genreHttp from "../../util/http/genre-http";
import categoryHttp from "../../util/http/category-http";

const useStyles = makeStyles((theme: Theme) => {
   return {
       submit: {
           margin: theme.spacing(1)
       }
   }
});

export const Form = () => {
    const classes = useStyles();
    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "outlined"
    };
    const [categories, setCategories] = useState<DeepPartial<any>>([]);
    const {register, handleSubmit, getValues, watch} = useForm(
        {
            defaultValues: {
                categories_id: categories
            }
        }
    );
    useEffect(() => {
        register({name: "categories_id"})
    }, [register]);

    useEffect(() => {
        categoryHttp
            .list()
            .then((response) => setCategories(response.data.data))
    }, []);

    function onSubmit(formData, event) {
        console.log(event);
        genreHttp
            .create(formData)
            .then((response) => console.log(response));
    };

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name="name"
                label="Nome"
                fullWidth
                variant="outlined"
                margin={"normal"}
                inputRef={register}
            />
            <TextField
                name="categories_id"
                select
                value={watch("categories_id")}
                label="Categorias"
                margin="normal"
                variant="outlined"
                fullWidth
                onChange={(e) => {
                    // setValue("categories_id", (e.target.value as DeepPartial<any>));
                }}
                SelectProps={
                    {multiple: true}
                }
            >
                <MenuItem value="" disabled>
                    <em>Selecione Categorias</em>
                </MenuItem>
                {
                    categories.map(
                        (category, key) => (
                            <MenuItem key={key} value={category.id}> {category.name} </MenuItem>
                        )
                    )
                }
            </TextField>
            <Box dir={"rtl"}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar e voltar</Button>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
            </Box>
        </form>
    );
};