<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice No.</th>
                <th>Product Name/Code</th>
                <th>Category</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Payable</th>
                <th>Pay By</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($orders as $k => $order)
                @foreach ($order->items as $iIndex => $item)
                    <tr>
                        @if($loop->first)
                            <td class="text-center" rowspan="{{ $order->items_count }}">
                                {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i:s') }}
                            </td>
                            <td class="text-center" rowspan="{{ $order->items_count }}">
                                {{ $order->order_no }}
                            </td>
                        @endif

                        <td>{{ $item?->item_name }} | {{ $item?->additional?->product?->sku }}</td>
                        <td>
                            @foreach($item?->additional?->product?->categories as $idx => $cate)
                                {{ $cate?->name }}
                                    @if(count($item?->additional?->product?->categories) !== ($idx + 1))
                                        ,
                                    @endif
                            @endforeach
                        </td>
                        <td>{{ $item?->size }}</td>
                        <td>{{ $item?->quantity }}</td>
                        <td>{{ $item?->regular_price }}</td>
                        @if($loop->first)
                            <td class="text-center" rowspan="{{ $order->items_count }}">
                                {{ $order->discount ?? 0 }}
                            </td>
                            <td class="text-center" rowspan="{{ $order->items_count }}">
                                {{ ($order->total ?? 0) - ($order->discount ?? 0) }}
                            </td>
                            <td class="text-center" rowspan="{{ $order->items_count }}">
                                {{ $order->payment_method }}
                            </td>
                        @endif


                    </tr>
                @endforeach
            @endforeach
        </tbody>

    </table>

</body>
</html>
