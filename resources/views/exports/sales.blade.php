<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
<table>
    <thead>
        <tr>
            <th>SL</th>
            <th>Order No</th>
            <th>Icon</th>
            <th>Product Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Size</th>
            <th>Color</th>
            <th>Branch</th>
            <th>Sale Type</th>
            <th>Saled By</th>
            <th>Sales</th>
            <th>Date Time</th>
        </tr>
    </thead>

    <tbody>
        @foreach($sales as $k => $sale)
            <tr>
                <td>
                    {{ ++$k }}

                </td>
                <td>
                    {{ $sale->orderitems?->order_no }}

                </td>
                <td>
                    @if(!empty($sale?->media?->color_thumbnails))
                        <a href="{{ asset('/products/'.$sale?->media?->color_thumbnails) }}" target="_blank">Click here</a>
                    @endif
                </td>
                <td>
                    {{ $sale?->product?->name }}
                </td>
                <td>
                    {{ $sale?->product?->brand?->name }}
                </td>
                <td>
                    @foreach($sale?->product?->categories as $idx => $category)
                        {{ $category?->name }}
                            @if(count($sale?->product?->categories) !== -1)
                                ,
                            @endif

                    @endforeach
                </td>
                <td>
                    {{ $sale?->size }}
                </td>
                <td>
                    {{ $sale?->color }}

                </td>
                <td>
                    {{ $sale?->branchs?->name }}

                </td>
                <td>
                    {{ $orderTypeMap[$sale?->orderitems?->order?->order_type] ?? 'Manual' }}

                </td>
                <td>
                    {{ $sale?->orderitems?->order?->createdBy->name }}

                </td>
                <td>
                    {{ $sale?->orderitems_sum_quantity > 0 ? $sale?->orderitems_sum_quantity : 0 }}
                </td>
                <td>
                    {{ $sale?->created_at }}

                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- @php

$orderTypeMap = [
    'Pre Order',
    'Regular',
    'POS'
 ]

@endphp --}}

</body>
</html>
