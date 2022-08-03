<?php

namespace Infinety\Filemanager;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as BaseTool;

class FilemanagerTool extends BaseTool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-filemanager', __DIR__.'/../dist/js/tool.js');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('Filemanager')
            ->path('/nova-filemanager')
            ->icon('folder');
    }
}

