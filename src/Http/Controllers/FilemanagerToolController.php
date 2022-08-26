<?php

namespace Grayloon\Filemanager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Grayloon\Filemanager\Http\Services\FileManagerService;
use Laravel\Nova\Http\Requests\NovaRequest;

class FilemanagerToolController extends Controller
{
    /**
     * @var mixed
     */
    protected $service;

    /**
     * @param FileManagerService $filemanagerService
     */
    public function __construct(FileManagerService $filemanagerService)
    {
        $this->service = $filemanagerService;
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function getData(Request $request): JsonResponse
    {
        return $this->service->ajaxGetFilesAndFolders($request);
    }

	/**
	 * @param $resource
	 * @param $attribute
	 * @param NovaRequest $request
	 * @return JsonResponse
	 */
    public function getDataField($resource, $attribute, NovaRequest $request): JsonResponse
    {
        return $this->service->ajaxGetFilesAndFolders($request);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @throws \League\Flysystem\FilesystemException
	 */
    public function createFolder(Request $request): JsonResponse
    {
        return $this->service->createFolderOnPath($request->folder, $request->current);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function deleteFolder(Request $request): JsonResponse
    {
        return $this->service->deleteDirectory($request->current);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
    public function upload(Request $request): JsonResponse
    {
        $uploadingFolder = $request->folder ?? false;

        return $this->service->uploadFile(
            $request->file,
            $request->current ?? '',
            $request->visibility,
            $uploadingFolder,
            $request->rules ? $this->getRules($request->rules) : []
        );
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function move(Request $request): JsonResponse
    {
        return $this->service->moveFile($request->old, $request->path);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function getInfo(Request $request): JsonResponse
    {
        return $this->service->getFileInfo($request->file);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function removeFile(Request $request): JsonResponse
    {
        return $this->service->removeFile($request->file, $request->type);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function renameFile(Request $request): JsonResponse
    {
        return $this->service->renameFile($request->file, $request->name);
    }

	/**
	 * @param Request $request
	 * @return mixed
	 */
    public function downloadFile(Request $request): mixed
    {
        return $this->service->downloadFile($request->file);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function rename(Request $request): JsonResponse
    {
        return $this->service->renameFile($request->path, $request->name);
    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function folderUploadedEvent(Request $request): JsonResponse
    {
        return $this->service->folderUploadedEvent($request->path);
    }

    /**
     * Get rules in array way.
     *
     * @param string $rules
     *
     * @return  array
     */
    private function getRules(string $rules): array
    {
        return json_decode($rules);
    }
}
