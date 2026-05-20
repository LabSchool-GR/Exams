@php
    $siteName = config('app.name', __('navigation.app_name'));
    $sectionTitle = trim($__env->yieldContent('title'));
    $sectionDescription = trim($__env->yieldContent('description'));
    $sectionImage = trim($__env->yieldContent('meta_image'));
    $sectionImageAlt = trim($__env->yieldContent('meta_image_alt'));
    $sectionRobots = trim($__env->yieldContent('robots'));

    $resolvedLocale = str_replace('-', '_', app()->getLocale());
    $isEnglishLocale = \Illuminate\Support\Str::startsWith($resolvedLocale, 'en');

    $defaultDescription = $isEnglishLocale
        ? 'LabSchool Exams is an open-source knowledge assessment application for schools, training providers, and educational programs.'
        : 'Το LabSchool Exams είναι εφαρμογή αξιολόγησης γνώσεων ανοιχτού κώδικα για σχολεία, φορείς κατάρτισης και εκπαιδευτικά προγράμματα.';

    $metaTitle = $seoTitle ?? ($sectionTitle !== '' ? $sectionTitle : $siteName);
    $metaDescription = $seoDescription ?? ($sectionDescription !== '' ? $sectionDescription : $defaultDescription);
    $metaDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $metaDescription)));

    $metaUrl = $seoUrl ?? request()->fullUrl();
    $metaType = $seoType ?? 'website';
    $metaImage = $seoImage ?? ($sectionImage !== '' ? $sectionImage : asset('images/og-exams.png'));
    $metaImageAlt = $seoImageAlt ?? ($sectionImageAlt !== '' ? $sectionImageAlt : $siteName);
    $metaImageType = $seoImageType ?? 'image/png';
    $metaImageWidth = $seoImageWidth ?? 1200;
    $metaImageHeight = $seoImageHeight ?? 630;
    $metaRobots = $seoRobots ?? ($sectionRobots !== '' ? $sectionRobots : 'index, follow');
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="theme-color" content="#2563eb">
<meta name="robots" content="{{ $metaRobots }}">
<link rel="canonical" href="{{ $metaUrl }}">

<meta property="og:locale" content="{{ $resolvedLocale }}">
<meta property="og:type" content="{{ $metaType }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
<meta property="og:image:type" content="{{ $metaImageType }}">
<meta property="og:image:width" content="{{ $metaImageWidth }}">
<meta property="og:image:height" content="{{ $metaImageHeight }}">
<meta property="og:image:alt" content="{{ $metaImageAlt }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
