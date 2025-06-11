import React, {useEffect, useMemo, useState} from 'react';
import {Head, router, useForm, usePage} from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import Carousal from "@/Components/Core/Carousal.jsx";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter.jsx";



function Show({ product, variationOptions }) {
    let option_ids;
    let quantity;
    let price;
    const form = useForm({
        option_ids: {},
        quantity: 1,
        price: null,
    });

    const {url} = usePage();

    const [selectedOptions, setSelectedOptions] = useState([]);

    const images = useMemo(() => {
        for (let typeId in selectedOptions) {
            const option = selectedOptions[typeId];
            if(option.images.length > 0) return option.images;
        }

        return product.images;

    }, [product, selectedOptions]);

    const computeProduct = useMemo(() => {
        const selectedOptionIds = Object.values(selectedOptions).map(op => op.id).sort();
        for (let variation of product.variations){
            const optionIds = variation.variation_type_option_ids.sort();
            if(arrayAreEqual(selectedOptionIds, optionIds)){
                return {
                    price: variation.price,
                    quantity: variation.quantity === null ? Number.MAX_VALUE: variation.quantity,
                }
            }
        }

        return {
            price: product.price,
            quantity: product.quantity ?? Number.MAX_VALUE,
        };
    }, [product, selectedOptions]);

    useEffect(() => {
        for (let type of product.variationTypes) {
            const selectedOptionId = variationOptions[type.id];
            chooseOption(type.id, type.options.find(option => option.id === selectedOptionId) || type.options[0], false);
        }
    }, []);

    const getOptionsIdsMap = ((newOptions) => {
            return Object.fromEntries(Object.entries(newOptions).map(([a, b]) => [a, b.id]));
    })

    function arrayAreEqual(arr1, arr2) {
        if (arr1.length !== arr2.length) return false;

        const sorted1 = [...arr1].sort();
        const sorted2 = [...arr2].sort();

        return sorted1.every((val, index) => val === sorted2[index]);
    }

    function chooseOption(typeId, option, updateRouter = true) {
        setSelectedOptions((prevSelectedOptions) => {
            const newOptions = {
                ...prevSelectedOptions,
                [typeId]: option
            }

            if(updateRouter) {
                router.get(url, {
                    options: getOptionsIdsMap(newOptions)
                }, {
                    preserveState: true,
                    preserveScroll: true
                })
            }

            return newOptions;
        })
    }

    const onQuantityChange = (ev) => {
        const value = parseInt(ev.target.value) || 1;
        form.setData('quantity', value);
    }

    const addToCart = () => {
        form.post(route('cart.store', product.id), {

        }, {
            preserveState: true,
            preserveScroll: true,
            onError: (e) => {
                console.log('error', e);
            }
        })
    }

    const renderProductVariationTypes = () => {
        return (
                product.variationTypes.map((type, i) => (
                    <div key={type.id}>
                        <b>{type.name}</b>
                        <hr />
                        {type.type === 'Image' &&
                            <div className="flex gap-2 my-4">
                                {type.options.map(option => (
                                    <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                                        {option.images &&
                                        <img src={option.images[0].thumb} alt="" className={'w-[50px] ' + (selectedOptions[type.id]?.id === option.id? 'outline outline-4 outline-primary': '')} />
                                            }
                                    </div>
                                ))}
                            </div>
                        }

                        {(type.type === 'Select' || type.type === 'Radio') &&
                            <div className="flex join m-4">
                                {
                                    type.options.map(option => (<input onChange={ () => chooseOption(type.id, option) } key={option.id}
                                                                       className="join-item btn"
                                                                       type="radio"
                                                                       value={option.id}
                                                                       checked={selectedOptions[type.id]?.id === option.id}
                                                                       name={'variation_type_' + type.id}
                                                                       aria-label={option.name}/>
                                    ))
                                }
                            </div>}
                    </div>
            ))
        )
    }
    const renderAddToCartButton = () => {
        return (
            <div className="mb-8 flex gap-4">
                <select value={form.data.quantity} onChange={onQuantityChange} className="select select-bordered w-full">
                    {
                        Array.from({
                            length: Math.min(10, computeProduct.quantity)
                        }).map((el, i) => (
                            <option value={i + 1} key={i + 1}>Quantity: {i + 1}</option>
                        ))
                    }

                </select>
                <button className="btn btn-primary" onClick={addToCart}>Add To Cart</button>
            </div>
        )
    }

    useEffect(() => {
        const idsMap = Object.fromEntries(Object.entries(selectedOptions).map(([typeId, option]) => [typeId, option.id]))
        // console.log(idsMap)
        form.setData('option_ids', idsMap)
    }, [selectedOptions]);


    return (
        <AuthenticatedLayout>
            <Head title={product.title} />
            <div className="mx-auto p-8">
                <div className="grid gap-8 grid-cols-1 lg:grid-cols-12">
                    <div className="col-span-7">
                        <Carousal images={images} />
                    </div>
                    <div className="col-span-5">
                        <h1 className="text-2xl mb-8">{product.title}</h1>
                        <div className="text-3xl font-semibold">
                            <CurrencyFormatter amount={computeProduct.price} currency="USD" locale="en-US" />
                        </div>


                        {renderProductVariationTypes()}

                        {computeProduct.quantity !== undefined && computeProduct.quantity < 10 &&
                            <div className="text-error my-4">
                                <span>Only {computeProduct.quantity} left</span>
                            </div>
                        }

                        {renderAddToCartButton()}

                        <b className="text-xl">About the Item</b>

                        <div className="wysiwyg-output" dangerouslySetInnerHTML={{__html: product.description}} />
                    </div>


                </div>
            </div>
        </AuthenticatedLayout>
    );
}



export default Show;
