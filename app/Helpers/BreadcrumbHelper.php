<?php

if (!function_exists('getBreadcrumbs')) {
    /**
     * Generate breadcrumbs based on the current route name.
     *
     * @return array
     */
    function getBreadcrumbs()
    {
        $breadcrumbs = [];

        if (auth()->user()->hasRole(['organiser'])) {
            $url = url('/org/dashboard');
        } elseif (auth()->user()->hasRole('admin')) {
            $url = url('/admin/dashboard');
        } else {
            $url = url('/usr/dashboard');
        }

        // Always start with Home
        $breadcrumbs[] = [
            'title' => 'Home',
            'url'   => $url,
        ];



        // Get the current route name (e.g., "events", "reports", etc.)
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();

        if ($routeName) {
            // Convert route name (e.g., "my-team") to a more friendly title ("My Team")
            $title = ucwords(str_replace('-', ' ', $routeName));

            // Use the URL helper to generate the URL for this route
            // This assumes that your route URL corresponds to the route name.
            // If not, you may need to maintain a mapping array.
            $url = route($routeName);

            $breadcrumbs[] = [
                'title' => $title,
                'url'   => $url,
            ];
        }

        return $breadcrumbs;
    }
}
