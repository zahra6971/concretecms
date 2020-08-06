<?php

namespace Concrete\Controller\Dialog\Logs\Bulk;

use Concrete\Controller\Backend\UserInterface as BackendInterfaceController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Page\EditResponse;
use Concrete\Core\Permission\Key\Key;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\User;

class Delete extends BackendInterfaceController
{
    protected $viewPath = '/dialogs/logs/bulk/delete';
    protected $pages;
    protected $canEdit = false;

    protected function canAccess()
    {
        $taskPermission = Key::getByHandle("delete_log_entries");
        if (is_object($taskPermission)) {
            return $taskPermission->validate();
        } else {
            // This is a previous concrete5 versions that don't have the new task permission installed
            $app = Application::getFacadeApplication();
            $u = $app->make(User::class);
            return $u->isRegistered();
        }
    }

    public function view()
    {
        /** @var Request $request */
        $request = $this->app->make(Request::class);
        $logItems = (array)$request->query->get("item", []);
        $this->set('logItems', $logItems);
    }

    public function submit()
    {
        /** @var Request $request */
        $request = $this->app->make(Request::class);
        /** @var Connection $db */
        $db = $this->app->make(Connection::class);
        /** @var ResponseFactory $responseFactory */
        $responseFactory = $this->app->make(ResponseFactory::class);
        $logItems = (array)$request->request->get("logItem",[]);

        foreach($logItems as $logItem) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection SqlDialectInspection */
            /** @noinspection SqlNoDataSourceInspection */
            $db->executeQuery("DELETE FROM Logs WHERE logID = ?", [$logItem]);
        }

        $editResponse = new EditResponse();
        $editResponse->setMessage(t('Log entries successfully deleted.'));
        return $responseFactory->json($editResponse->getJSONObject());
    }


}
