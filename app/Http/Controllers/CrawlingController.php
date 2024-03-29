<?php

namespace App\Http\Controllers;

use App\Http\Requests\CrawlingRequest;
use App\Http\Requests\QueueRequest;
use App\Models\Queue;
use App\Services\XClient;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class CrawlingController extends Controller
{
    public function crawling(CrawlingRequest $request)
    {
        $client = app(XClient::class);
        $method = strtolower($request->input('method'));

        $content = Cache::remember(
            md5(serialize($request->toArray())),
            60 * 60,
            function () use ($client, $method, $request) {
                return $client
                    ->{$method}(
                        $request->input('url'),
                        $request->input('payload'),
                        $request->input('options')
                    )
                    ->getBody();
            }
        );

        return response($content);
    }

    public function queue(QueueRequest $request)
    {
        Queue::create([
            'url' => $request->input('url'),
            'method' => $request->input('method'),
            'payload' => $request->input('payload'),
            'options' => $request->input('options'),
            'callback_url' => $request->input('callback_url'),
            'status' => Queue::STATUS_PENDING,
        ]);

        return response()->noContent();
    }
}
