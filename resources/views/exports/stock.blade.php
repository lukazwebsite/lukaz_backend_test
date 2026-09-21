<table>
    <thead>
        <tr class="bg-gray-400">
            <th class="text-center">SL</th>
            <th class="text-center">Branch</th>
            <th class="text-center">Category</th>
            <th class="text-left px-2">Product Name</th>
            <th class="text-left px-2">Product Code</th>
            <th class="text-left w-12 px-2">Photo Link</th>
            <th class="text-center">Color</th>
            <th class="text-center">Regular Price</th>
            <th class="text-center">Current Price</th>
            @foreach ($sizes as $size)
                <th class="text-center">{{ $size }}</th>
            @endforeach

            <th class="text-center">Total Quantity</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($branchWiseStocks as $index => $stock )
            <tr>
                <td class="text-center">{{ ++$index }}</td>
                <td>{{ $stock?->branchs?->name }}</td>
                <td>
                    @foreach ($stock?->product?->categories ?? [] as $idx => $category)

                        {{ $category?->name }} @if($idx !== ($stock?->product?->categories->count() - 1)) , @endif

                    @endforeach

                </td>
                <td>{{ $stock?->product?->name }}</td>
                <td>{{ $stock?->product?->sku }}</td>
                <td class="w-12 !p-0 object-cover">
                    @if(!empty($stock?->media?->color_thumbnails))
                        <a href="{{ asset($stock?->media?->color_thumbnails ? '/products/'.$stock?->media?->color_thumbnails : '/assets/sites/sample.webp') }}" target="_blank">Click here</a>
                    @endif
                </td>

                <td>{{ $stock?->color }} {{ $stock?->size }}</td>
                <td class="text-center">{{ $stock?->regular_price }}</td>
                <td class="text-center">{{ $stock?->current_price }}</td>

                @foreach($sizes as $size)
                    @php
                        $col = 'size_'.preg_replace('/[^a-zA-Z0-9]/','_',$size);
                    @endphp
                <td>
                    {{ $stock->$col ?? 0 }}
                </td>
                @endforeach

                <td>{{ getTotal($stock->toArray()) }}</td>
            </tr>
        @endforeach

    </tbody>




</table>

@php

function getTotal($row)
    {
        $total = 0;

        foreach ($row as $key => $value) {
            if (str_starts_with($key, 'size_')) {
                $total += (float) $value;
            }
        }

        return $total;
    }
@endphp
