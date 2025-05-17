<div class="dashboard-main-body">
    <div class="card h-100 p-0 radius-12 overflow-hidden">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ $lang->data['file_tools'] ?? 'File Tools' }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="tw-justify-center tw-items-center tw-flex tw-w-full">
                    <div class="mb-20 tw-flex tw-flex-col tw-items-center gap-2">
                        <div class="upload-image-wrapper d-flex align-items-center gap-3 flex-wrap">
                            <div class="uploaded-imgs-container d-flex gap-3 flex-wrap"></div>
                            <label class="upload-file-multiple h-120-px w-120-px border input-form-light radius-8 overflow-hidden border-dashed bg-neutral-50 bg-hover-neutral-200 d-flex align-items-center flex-column justify-content-center gap-1">
                                <iconify-icon icon="solar:camera-outline" class="text-xl text-secondary-light"></iconify-icon>
                                <span class="fw-semibold text-secondary-light">Upload</span>
                                <input wire:model="photo" id="upload-file-multiple" class="image-file" type="file" hidden>
                            </label>
                        </div>
                        <div class="col-md-9 pt-2">
                            <div class="row justify-content-center align-items-center">
                                <div class="col-8">
                                    @error('photo')
                                    <span class="text-danger text-xs liveerror" id="live_error">{{$message}}</span>
                                    @enderror
                                    <div wire:ignore>
                                        <span class="text-danger text-xs" id="myerror2"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tw-text-sm tw-mt-4">Important! Upload ".png" images or icons only & the image name should not contain any spaces.</div>
                    </div>
                </div>
                <div class="tw-grid tw-grid-cols-12 tw-gap-4 tw-mt-2">
                    @foreach ($icons as $key => $value)
                    <a type="button">
                        <img src="{{ asset('assets/img/service-icons/' . $value['path']) }}" class="tw-w-full tw-bg-blue-200/10 p-4 tw-rounded-lg" alt="">
                    </a>
                    @endforeach
                </div>

                <div class="d-flex align-items-center justify-content-center gap-3 mt-24">
                <button type="button" 
        wire:click="save" 
        class="btn btn-primary border border-primary-600 text-md px-24 py-12 radius-8" 
        @if(!$allowupload) disabled @endif>
    <div class="spinner-border spinner-border-sm mx-1" role="status" wire:loading wire:target="save">
        <span class="visually-hidden">Loading...</span>
    </div>
    {{$lang->data['save'] ?? 'Save'}}
</button>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        document.addEventListener('livewire:load', function() {
            // Clear custom error when Livewire updates
            Livewire.on('removelocalError', () => {
                $('#myerror2').text("");
            });

            // Basic file validation before Livewire handles it
            $('.image-file').on('change', function(e) {
                $('#myerror2').text("");
                const file = this.files[0];
                
                if (!file) return;
                
                // Check file size (in MB)
                const fileSizeMB = file.size / 1024 / 1024;
                if (fileSizeMB > 1) {
                    $('#myerror2').text('File Size Is Above 1 MB!');
                    this.value = ''; // Clear the file input
                    @this.set('photo', null);
                    @this.set('allowupload', false);
                    return;
                }
                
                // Check file extension
                const fileName = file.name;
                const ext = fileName.split('.').pop().toLowerCase();
                if (ext !== 'png') {
                    $('#myerror2').text('Only PNG files are allowed!');
                    this.value = ''; // Clear the file input
                    @this.set('photo', null);
                    @this.set('allowupload', false);
                    return;
                }
                
                // Check for spaces in filename
                if (fileName.includes(' ')) {
                    $('#myerror2').text('Filename should not contain spaces!');
                    this.value = ''; // Clear the file input
                    @this.set('photo', null);
                    @this.set('allowupload', false);
                    return;
                }
            });
        });
    </script>
    @endpush
</div>
