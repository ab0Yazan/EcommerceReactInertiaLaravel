import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import ProductItem from "@/Components/App/ProductItem.jsx";

export default function Home({ products }) {
    return (
        <>
            <AuthenticatedLayout>
                <Head title="Home" />
                <div>
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 p-8">
                        {
                            products.data.map((product) => (

                                <ProductItem product={product} key={product.id} />
                            ))
                        }
                    </div>
                </div>
            </AuthenticatedLayout>

        </>
    );
}
