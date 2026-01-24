@extends('admin.layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet" />
    <style>
        :root {
            --z-accent-color: {{$settings['accentColor']}};
        }
    </style>
@endsection

@section('body-content')
    <div id="react-root"></div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/common/lib/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
    <script>
        const settings = @json($settings);
    </script>
    <script src="{{ asset('js/client/admin/roots/app.js') }}"></script>
@endsection
