<div class="dashboard-main-body">
    <div class="card h-100 p-0">
        <div class="tw-py-1.5 tw-px-3 bg-base d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <form class="navbar-search">
                    <input type="text" class="bg-base tw-px-3 tw-py-1.5 w-auto" name="search" placeholder="{{ $lang->data['search_here'] ?? 'Search Here' }}" wire:model.live="search_query">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            @can('order_create')
            <a href="{{route('orders.pos')}}" type="button" class="btn btn-primary text-sm btn-sm radius-8 d-flex align-items-center gap-2" >
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                {{ $lang->data['add_new_order'] ?? 'Add New Order' }}
            </a>
            @endcan
        </div>
        <div class="tw-p-0">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0">
                  <thead>
                    <tr>
                      <th scope="col" class="">{{ $lang->data['order_info'] ?? 'Order Info' }}</th>
                      <th scope="col" class="">{{ $lang->data['customer'] ?? 'Customer' }}</th>
                      <th scope="col" class="">{{ $lang->data['order_amount'] ?? 'Order Amount' }}</th>
                      <th scope="col" class=""> {{ $lang->data['status'] ?? 'Status' }}</th>
                      <th scope="col" class="">{{ $lang->data['payment'] ?? 'Payment' }}</th>
                      <th scope="col" class=""> {{ $lang->data['order_source'] ?? 'Created By' }}</th>
                      <th scope="col" class="text-center">{{ $lang->data['action'] ?? 'Action' }}</th>
                    </tr>
                  </thead>
                  <tbody>
                        @foreach ($orders as $item)
                        <tr class="tw-text-xs">
                            <td>
                                <div class="tw-flex tw-flex-col">
                                    <div class="text-neutral-600">
                                        {{ $lang->data['order_id'] ?? 'Order ID' }} : <span class="tw-font-medium text-primary-light">{{ $item->order_number }}</span> 
                                    </div>
                                    <div class="text-neutral-600">
                                        {{ $lang->data['Pick up Date'] ?? 'Pick up Date' }} : <span class="tw-font-medium text-primary-light">{{ \Carbon\Carbon::parse($item->order_date)->format('d/m/y') }}</span> 
                                    </div>
                                    <div class="text-neutral-600">
                                        {{ $lang->data['Pick up time'] ?? 'Pick up Time' }} : <span class="tw-font-medium text-primary-light">{{ \Carbon\Carbon::parse($item->delivery_time)->format('H:i') }}</span> 
                                    </div>
                                    <div class="text-neutral-600">
                                        {{ $lang->data['delivery_date'] ?? 'Delivery Date' }} : <span class="tw-font-medium text-primary-light">{{ \Carbon\Carbon::parse($item->delivery_date)->format('d/m/y') }}</span> 
                                    </div>

                                </div>
                            </td>
                            <td class="">
                                <p>{{ $item->customer_name ?? ($lang->data['walk_in_customer'] ?? 'Walk In Customer') }}</p>
                                <p>{{$item->phone_number ? (int)$item->phone_number : ''}}</p>
                            @if(!empty($item->address))
                                <p>{{ $item->address }}</p>
                            @else
                                <p>{{ $lang->data['no_address'] ?? 'No Address Available' }}</p>
                            @endif
                            </td>
                            <td class="text-primary">
                                {{ getFormattedCurrency($item->total) }}
                            </td>
                              <td class="">
                                @if ($item->status == 0)
                                <span class="badge  fw-semibold text-neutral-600 bg-neutral-200 px-20 py-9 radius-4 text-white">
                                    {{ $lang->data['pending'] ?? 'Pending' }}
                                </span>
                                @elseif($item->status == 1)
                                <span class="badge  fw-semibold text-primary-600 bg-primary-100 px-20 py-9 radius-4 text-white">
                                    {{ $lang->data['Pick-up'] ?? 'Pick-up' }}
                                </span>
                                @elseif($item->status == 2)
                                <span class="badge  fw-semibold text-warning-600 bg-warning-100 px-20 py-9 radius-4 text-white">
                                    {{ $lang->data['processing'] ?? 'Processing' }}
                                </span>
                                @elseif($item->status ==3)
                                <span class="badge  fw-semibold text-info-600 bg-info-100 px-20 py-9 radius-4 text-white">
                                    {{ $lang->data['ready_to_deliver'] ?? 'Ready To Deliver' }}
                                </span>
                                @elseif($item->status == 4)
                                <span class="badge  fw-semibold text-success-600 bg-success-100 px-20 py-9 radius-4 text-white">
                                    {{ $lang->data['delivered'] ?? 'Delivered' }}
                                </span>
                                @elseif($item->status == 5)
                                <span class="badge  fw-semibold text-danger-600 bg-danger-100 px-20 py-9 radius-4 text-white">
                                    {{ $lang->data['returned'] ?? 'Returned' }}
                                </span>
                                @endif
                            </td>
                            <td>
                                @php
                                $paidamount = \App\Models\Payment::where('order_id', $item->id)->sum('received_amount');
                                @endphp
                                <div class="tw-flex tw-flex-col">
    <div class="text-neutral-600">
        {{ $lang->data['total_amount'] ?? 'Total Amount' }} : 
        <span class="tw-font-medium text-primary-light">{{ getFormattedCurrency($item->total) }}</span> 
    </div>

    <div class="text-neutral-600">
        @php
            $current_paid_amount = \App\Models\Payment::where('order_id', $item->id)->sum('received_amount');
        @endphp
        {{ $lang->data['paid_amount'] ?? 'Paid Amount' }} : 
        <span class="tw-font-medium text-primary-light">{{ getFormattedCurrency($current_paid_amount) }}</span> 
    </div>
 	@php
    $isFullyPaid = $item->total == $current_paid_amount && $item->total > 0;
    $isUnpaid = ($item->total > $current_paid_amount || ($item->total == 0 && $current_paid_amount == 0)) && $item->status != 4;
@endphp

@if ($isFullyPaid)
    <div class="tw-mt-1">
        <button type="button" class="btn rounded-pill btn-neutral-300 text-neutral-600 radius-8 tw-text-xs tw-py-1 tw-px-2">
            {{ $lang->data['fully_paid'] ?? 'Fully Paid' }}
        </button>
    </div>
@elseif ($isUnpaid)
    <div class="tw-mt-1">
        <button type="button" class="btn rounded-pill btn-success-100 text-success-600 radius-8 tw-text-xs tw-py-1 tw-px-2">
            {{ $lang->data['Unpaid'] ?? 'Unpaid' }}
        </button>
    </div>
@endif
</div>
                            </td>
                            <td class="">
                            <div class="text-neutral-600">
                                        {{ $lang->data['order_source'] ?? 'Arrived From' }} : <span class="tw-font-medium text-primary-light">{{ $item->order_source }}</span> 
                                    </div>
                                
                            </td>
                            <td class="text-center"> 
                                <div class="d-flex align-items-center gap-10 justify-content-center">
                                    @can('order_view')
                                    <a href="{{route('order.view',$item->id)}}" type="button" class="bg-success-100 text-success-600 bg-hover-success-200 fw-medium tw-size-8 d-flex justify-content-center align-items-center rounded-circle" >
                                        <iconify-icon icon="lucide:eye" class="menu-icon"></iconify-icon>
                                    </a>
                                    @endcan
                                    @can('order_print')
                                    <a href="{{route('order.print',$item->id)}}" target="_blank" class="bg-warning-100 text-warning-600 bg-hover-warning-200 fw-medium tw-size-8 d-flex justify-content-center align-items-center rounded-circle" >
                                        <iconify-icon icon="material-symbols-light:print-outline" class="menu-icon tw-text-xl"></iconify-icon>
                                    </a>
                                    @endcan
                                    @can('order_edit')
                                    <a href="{{route('orders.pos.edit',$item->id)}}" class="bg-info-100 text-info-600 bg-hover-info-200 fw-medium tw-size-8 d-flex justify-content-center align-items-center rounded-circle" >
                                        <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                    </a>
                                    @endcan
                                    @can('order_delete')
                                    <button type="button" wire:click.prevent="deleteOrder({{$item->id}})" class="remove-item-button bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium tw-size-8 d-flex justify-content-center align-items-center rounded-circle"> 
                                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                    </button>
                                    @endcan
                                </div>
                            </td> 
                        </tr>
                        @endforeach
                  </tbody>
                </table>
                @if(count($orders) == 0)
                    <x-empty-item/>
                @endif
                                @if($hasMorePages)
                <div x-data="{
    init() {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Check if $wire is available
                        if (typeof $wire !== 'undefined') {
                            $wire.loadOrders();
                        } else {
                            console.error('Livewire not initialized');
                        }
                    }
                });
            },
            { root: null }
        );
        
        observer.observe(this.$el);
        
        // Cleanup observer when component is removed
        this.$watch('$el.isConnected', isConnected => {
            if (!isConnected) {
                observer.disconnect();
            }
        });
    }
}">
                @endif
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header py-16 px-24 border border-top-0 border-start-0 border-end-0">
                    <h1 class="modal-title text-md" id="exampleModalLabel">{{ $lang->data['payment_details'] ?? 'Payment Details' }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if ($order)
                    <div class="modal-body p-24">
                        <form action="#">
                            <div class="row">   
                                <div class="col-12">
                                    <div class="">
                                        <ul>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between tw-w-full">
                                                <span class="text-md fw-semibold text-primary-light">{{ $lang->data['customer'] ?? 'Customer' }} :</span>
                                                <span class="text-secondary-light fw-medium">{{ $customer_name }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between ">
                                                <span class="text-md fw-semibold text-primary-light"> {{ $lang->data['order_id'] ?? 'Order ID' }} :</span>
                                                <span class="text-secondary-light fw-medium">{{ $order->order_number }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between">
                                                <span class="text-md fw-semibold text-primary-light">  {{ $lang->data['order_date'] ?? 'Order Date' }} :</span>
                                                <span class="text-secondary-light fw-medium">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between">
                                                <span class="text-md fw-semibold text-primary-light">  {{ $lang->data['delivery_date'] ?? 'Delivery Date' }} :</span>
                                                <span class="text-secondary-light fw-medium">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between">
                                                <span class="text-md fw-semibold text-primary-light">  {{ $lang->data['delivery_time'] }} :</span>
                                                <span class="text-secondary-light fw-medium">{{ \Carbon\Carbon::parse($order->delivery_time) }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between">
                                                <span class="text-md fw-semibold text-primary-light"> {{ $lang->data['order_amount'] ?? 'Order Amount' }} :</span>
                                                <span class="text-secondary-light fw-medium"> {{ getFormattedCurrency($order->total) }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 mb-12 tw-justify-between">
                                                <span class="text-md fw-semibold text-primary-light"> {{ $lang->data['paid_amount'] ?? 'Paid Amount' }} :</span>
                                                <span class="text-secondary-light fw-medium"> {{ getFormattedCurrency($paid_amount) }}</span>
                                            </li>
                                            <li class="d-flex align-items-center gap-1 tw-justify-between">
                                                <span class="text-md fw-semibold text-primary-light"> {{ $lang->data['balance'] ?? 'Balance' }} :</span>
                                                <span class="text-secondary-light fw-medium"> {{ getFormattedCurrency($order->total - $paid_amount) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12 tw-my-6">
                                    <hr>
                                </div>
                                <div class="col-12 mb-20 ">
                                    <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">{{ $lang->data['paid_amount'] ?? 'Paid Amount' }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control radius-8" placeholder="{{ $lang->data['enter_amount'] ?? 'Enter Amount' }}" wire:model="balance" >
                                    @error('balance')
                                        <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-12 mb-20 ">
                                    <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">{{ $lang->data['payment_type'] ?? 'Payment Type' }} <span class="text-danger">*</span></label>
                                    <select  class="form-select radius-8" wire:model="payment_mode">
                                        <option value="">
                                            {{ $lang->data['choose_payment_type'] ?? 'Choose Payment Type' }}
                                        </option>
                                        <option class="select-box" value="1">
                                            {{ $lang->data['cash'] ?? 'Cash' }}
                                        </option>
                                        <option class="select-box" value="2">
                                            {{ $lang->data['upi'] ?? 'UPI' }}
                                        </option>
                                        <option class="select-box" value="3">
                                            {{ $lang->data['card'] ?? 'Card' }}
                                        </option>
                                        <option class="select-box" value="4">
                                            {{ $lang->data['cheque'] ?? 'Cheque' }}
                                        </option>
                                        <option class="select-box" value="5">
                                            {{ $lang->data['bank_transfer'] ?? 'Bank Transfer' }}
                                        </option>
                                    </select>
                                    @error('payment_mode')
                                    <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12 mb-20">
                                    <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">{{ $lang->data['notes'] ?? 'Notes' }} </label>
                                    <textarea class="form-control radius-8" placeholder="{{ $lang->data['enter_notes'] ?? 'Enter Notes' }}"  wire:model="note"></textarea>
                                    @error('note')
                                        <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="d-flex align-items-start justify-content-end gap-3 mt-24">
                                    <button data-bs-dismiss="modal" type="button" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-40 py-11 radius-8"> 
                                    {{ $lang->data['cancel'] ?? 'Cancel' }}
                                    </button>
                                    <button type="button" wire:click.prevent="addPayment()" class="btn btn-primary border border-primary-600 text-md px-24 py-12 radius-8"> 
                                    {{ $lang->data['save'] ?? 'Save' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
