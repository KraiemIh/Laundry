<div class="dashboard-main-body h-screen flex flex-col">
    <div class="card h-full p-0 radius-12 flex flex-col">
        <!-- Filtres en haut -->
        <div class="tw-py-1.5 tw-px-3 bg-base d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="tw-flex tw-items-center gap-4 flex-wrap">
                <!-- Date de début -->
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-1 tw-flex-col">
                        <span class="fw-medium">{{ $lang->data['start_date'] ?? 'Start Date' }}</span>
                        <input type="date" class="form-control bg-base h-40-px w-auto" wire:model.live="from_date">
                    </div>
                </div>
                <!-- Date de fin -->
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-1 tw-flex-col">
                        <span class="fw-medium">{{ $lang->data['end_date'] ?? 'End Date' }}</span>
                        <input type="date" class="form-control bg-base h-40-px w-auto" wire:model.live="to_date">
                    </div>
                </div>
                <!-- Statut -->
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-1 tw-flex-col">
                        <span class="fw-medium">{{ $lang->data['status'] ?? 'Status' }}</span>
                        <select class="form-select form-select-sm bg-base h-40-px w-auto" wire:model.live="status">
                            <option class="select-box" value="-1">{{ $lang->data['all_orders'] ?? 'All Orders' }}</option>
                            <option class="select-box" value="0">{{ $lang->data['pending'] ?? 'Pending' }}</option>
                            <option class="select-box" value="1">{{ $lang->data['Pick-up'] ?? 'Pick-up' }}</option>
                            <option class="select-box" value="2">{{ $lang->data['processing'] ?? 'Processing' }}</option>
                            <option class="select-box" value="3">{{ $lang->data['ready_to_deliver'] ?? 'Ready To Deliver' }}</option>
                            <option class="select-box" value="4">{{ $lang->data['delivered'] ?? 'Delivered' }}</option>
                            <option class="select-box" value="5">{{ $lang->data['returned'] ?? 'Returned' }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="card-body p-0 flex-grow overflow-hidden flex flex-col">
            <!-- Tableau scrollable -->
            <div class="table-responsive scroll-sm overflow-y-auto w-full max-h-screen">
                <table class="table bordered-table sm-table mb-0 table-striped w-full">
                    <thead>
                        <tr>
                            <th class="min-w-[100px]">{{ $lang->data['date'] ?? 'Date' }}</th>
                            <th class="min-w-[100px]">{{ $lang->data['order_id'] ?? 'Order ID' }}</th>
                            <th class="min-w-[100px]">{{ $lang->data['customer'] ?? 'Customer' }}</th>
                            <th class="min-w-[150px]">{{ $lang->data['Service'] ?? 'Service' }}</th>
                            <th class="min-w-[150px]">{{ $lang->data['address'] ?? 'Address' }}</th>
                            <th class="min-w-[150px]">{{ $lang->data['order_amount'] ?? 'Order Amount' }}</th>
                            <th class="min-w-[150px]">{{ $lang->data['status'] ?? 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $row)
                            <tr>
                                <td><p class="text-sm mb-0">{{ \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') }}</p></td>
                                <td><p class="text-sm font-weight-bold tw-text-black mb-0">{{ $row->order_number }}</p></td>
                                <td><p class="text-sm font-weight-bold tw-text-black mb-0">{{ $row->customer_name ?? "" }}</p></td>
                                <td>
                                    <div class="tw-flex tw-flex-col">
                                        @foreach($row->details as $detail)
                                            <div class="text-neutral-600">
                                                {{ $lang->data['service_name'] ?? 'Service' }} :
                                                <span class="tw-font-medium text-primary-light">
                                                    {{ $detail->service->service_name ?? 'Service supprimé' }}
                                                </span>
                                                @if($detail->service_name !== ($detail->service->service_name ?? null))
                                                    <span class="text-xs text-gray-500">
                                                        (Item: {{ $detail->service_name }})
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                          
                                <td>      @if(!empty($row->address))
                                <p>{{ $row->address ?? "" }}</p>
                            @else
                                <p>{{ $lang->data['no_address'] ?? 'No Address Available' }}</p>
                            @endif</td>
                                <td><p class="text-sm font-weight-bold text-primary mb-0">{{ getFormattedCurrency($row->total) }}</p></td>
                                <td>
                                    <span class="badge fw-semibold text-info-600 bg-info-100 px-20 py-9 radius-4 text-white">
                                        {{ getOrderStatus($row->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pied de page (totaux et boutons) -->
            <div class="tw-flex tw-items-center tw-justify-between tw-w-full tw-p-2 tw-gap-2 bg-base border-t">
                <div class="tw-flex tw-items-center tw-gap-4">
                    <div>{{ $lang->data['total_orders'] ?? 'Total Orders' }} : <span class="tw-font-bold">{{ count($orders) }}</span></div>
                    <div>{{ $lang->data['total_order_amount'] ?? 'Total Order Amount' }} : <span class="tw-font-bold">{{ getFormattedCurrency($orders->sum('total')) }}</span></div>
                </div>
                <div class="tw-flex tw-items-center tw-gap-2">
                    @can('report_download')
                        <button type="button" wire:click="downloadFile()" class="btn btn-warning-100 text-warning-600 radius-8 px-16 py-9">
                            {{ $lang->data['download_report'] ?? 'Download Report' }}
                        </button>
                    @endcan
                    @can('report_print')
                        <a href="{{ url('admin/reports/print-report/order/'.$from_date.'/'.$to_date.'/'.$status) }}" target="_blank">
                            <button type="button" class="btn btn-success-100 text-success-600 radius-8 px-16 py-9">
                                {{ $lang->data['print_report'] ?? 'Print Report' }}
                            </button>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
