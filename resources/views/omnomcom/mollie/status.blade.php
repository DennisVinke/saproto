@extends('website.layouts.panel')

@section('page-title')
    Mollie Transaction #{{ $transaction->id }}
@endsection

@section('panel-title')
    Mollie Transaction #{{ $transaction->id }}
@endsection

@section('panel-body')

    <table class="table borderless">
        <tr>
            <th>User</th>
            <td>{{ $transaction->user->name }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $mollie->description }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td>&euro;{{ number_format($mollie->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if(MollieTransaction::translateStatus($mollie->status) == 'open')
                    <span class="label label-default">{{ $mollie->status }}</span>
                    <a href="{{$mollie->links->paymentUrl}}">
                        <span class="label label-success">Continue Payment</span>
                    </a>
                @elseif(MollieTransaction::translateStatus($mollie->status) == 'paid')
                    <span class="label label-success">{{ $mollie->status }}</span>
                @elseif(MollieTransaction::translateStatus($mollie->status) == 'failed')
                    <span class="label label-danger">{{ $mollie->status }}</span>
                @else
                    <span class="label label-warning">{{ $mollie->status }}</span>
                @endif
            </td>
        </tr>
    </table>

    <hr>

    @if(MollieTransaction::translateStatus($mollie->status) == 'failed')
        <p>
            This payment has failed. All orderlines associated with this payment have been set back to unpaid. You can
            try to start a new payment.
        </p>
    @else
        <p>
            Below you can find all the orderlines associated with this payment.
        </p>

        <table class="table">
            <thead>
            <tr>
                <th>€</th>
                <th>Product</th>
                <th>Time</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transaction->orderlines as $orderline)
                <tr>
                    <td><strong>&euro;</strong> {{ number_format($orderline->total_price, 2, '.', '') }}</td>
                    <td>{{ $orderline->units }}x <strong>{{ $orderline->product->name }}</strong></td>
                    <td>{{ date('Y-m-d H:i:s', strtotime($orderline->created_at)) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

@endsection

@section('panel-footer')

    <a href="#" onclick="javascript:history.go(-1)" class="btn btn-default pull-right">Go Back</a>

@endsection