<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\EditorBundle\Entity\Permissions;
use Newscoop\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PermissionsController extends Controller
{
    /**
     * @Route("/admin/editor_plugin/settings/users/load", options={"expose"=true}, name="newscoop_editor_settings_loadusers")
     */
    public function loadUsersAction(Request $request)
    {
        $this->checkPermissions();
        $responseArray = $this->getUsersArray($request);

        return new JsonResponse($responseArray);
    }

    private function checkPermissions()
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if (!$user->hasPermission('plugin_editor_permissions')) {
            throw new AccessDeniedException();
        }
    }

    private function getUsersArray(Request $request)
    {
        $cacheService = $this->get('newscoop.cache');
        $em = $this->get('em');
        $userService = $this->get('user');
        $request->query->add($this->getAllUserGroups());
        $criteria = $userService->extractCriteriaFromRequest($request);
        $criteria->is_public = null;
        $registered = $userService->countBy(array('status' => User::STATUS_ACTIVE));
        $pending = $userService->countBy(array('status' => User::STATUS_INACTIVE));
        $cacheKey = array('users__'.md5(serialize($criteria)), $registered, $pending);
        $criteria->is_author = true;
        if ($cacheService->contains($cacheKey)) {
            $users = $cacheService->fetch($cacheKey);
        } else {
            $users = $em->getRepository('Newscoop\Entity\User')->getListByCriteria($criteria);
            $users = $this->getProcessedUsers($users);

            $cacheService->save($cacheKey, $users);
        }

        return $users;
    }

    private function getAllUserGroups()
    {
        $em = $this->get('em');
        $userGroups = $em->getRepository('Newscoop\Entity\User\Group')->findAll();
        $groups = array();
        foreach ($userGroups as $group) {
            $groups['user-group'][] = $group->getId();
        }

        return $groups;
    }

    private function getProcessedUsers($users)
    {
        $pocessed = array();
        foreach ($users as $user) {
            $pocessed[] = $this->processUser($user);
        }

        $responseArray = array(
            'records' => $pocessed,
            'assignedAll' => $this->checkCount($users),
            'queryRecordCount' => $users->count,
            'totalRecordCount'=> count($users->items)
        );

        return $responseArray;
    }

    private function processUser(User $user)
    {
        $em = $this->get('em');
        $types = array();
        foreach ($user->getUserTypes() as $type) {
            $types[] = $type->getName();
        }

        $userPermission = $em->getRepository('Newscoop\EditorBundle\Entity\Permissions')->findOneByUser($user);

        return array(
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'updated' => $userPermission ? $userPermission->getUpdatedAt()->format('Y-m-d H:i:s') : '-',
            'types' => implode(', ', $types),
            'assigned' => $userPermission ? $userPermission->getIsAssigned() : false,
        );
    }

    private function checkCount($users)
    {
        $em = $this->get('em');
        $assignAllMarked = true;
        $assignedCount = $em->getRepository('Newscoop\EditorBundle\Entity\Permissions')
            ->createQueryBuilder('p')
            ->select('count(p)')
            ->where('p.isAssigned = true')
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $assignedCount !== count($users)) {
            $assignAllMarked = false;
        }

        return $assignAllMarked;
    }

    /**
     * @Route("/admin/editor_plugin/settings/users/assign-all/", options={"expose"=true}, name="newscoop_editor_settings_assignall")
     */
    public function assignAllAction(Request $request)
    {
        $this->checkPermissions();
        $em = $this->get('em');
        $users = $this->getUsersArrayByCriteria($request);
        foreach ($users as $key => $user) {
            $this->assignOrCreatePermission($user['id']);
        }

        $em->flush();

        return new JsonResponse(array(
            'status' => true
        ));
    }

    /**
     * @Route("/admin/editor_plugin/settings/users/unassign-all/", options={"expose"=true}, name="newscoop_editor_settings_unassignall")
     */
    public function unassignAllAction(Request $request)
    {
        $this->checkPermissions();
        $em = $this->get('em');
        $users = $this->getUsersArrayByCriteria($request);
        foreach ($users as $key => $user) {
            $this->unassignSingleUser($user['id']);
        }

        $em->flush();

        return new JsonResponse(array(
            'status' => true
        ));
    }

    private function getUsersArrayByCriteria($request)
    {
        $em = $this->get('em');
        $userService = $this->get('user');
        $request->query->add($this->getAllUserGroups());
        $criteria = $userService->extractCriteriaFromRequest($request);
        $criteria->is_public = null;
        $criteria->is_author = true;

        $result = $em->getRepository('Newscoop\Entity\User')->getListByCriteria($criteria, false);
        $users = $result[0]->getQuery()->getArrayResult();

        return $users;
    }

    /**
     * @Route("/admin/editor_plugin/settings/users/unassign/{userId}", options={"expose"=true}, name="newscoop_editor_settings_unassignuser")
     */
    public function unassignUserAction(Request $request, $userId)
    {
        $this->checkPermissions();
        $status = false;
        $em = $this->get('em');
        if ($this->unassignSingleUser($userId)) {
            $status = true;
            $em->flush();
        }

        return new JsonResponse(array(
            'status' => $status
        ));
    }

    /**
     * @Route("/admin/editor_plugin/settings/users/assign/{userId}", options={"expose"=true}, name="newscoop_editor_settings_assignuser")
     */
    public function assignUserAction(Request $request, $userId)
    {
        $this->checkPermissions();
        $em = $this->get('em');
        try {
            $this->assignOrCreatePermission($userId);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'status' => false
            ));
        }

        return new JsonResponse(array(
            'status' => true
        ));
    }

    private function assignOrCreatePermission($userId)
    {
        if (!$this->assignSingleUser($userId)) {
            $this->persistUserPermission($userId);
        }
    }

    private function assignSingleUser($userId)
    {
        $em = $this->get('em');
        $userPermission = $em->getRepository('Newscoop\EditorBundle\Entity\Permissions')->findOneByUser($userId);
        if ($userPermission && !$userPermission->getIsAssigned()) {
            $userPermission->setIsAssigned(true);
            $userPermission->setUpdatedAt(new \DateTime());
        }

        return $userPermission;
    }

    private function unassignSingleUser($userId)
    {
        $em = $this->get('em');
        $userPermission = $em->getRepository('Newscoop\EditorBundle\Entity\Permissions')->findOneByUser($userId);
        if ($userPermission && $userPermission->getIsAssigned()) {
            $userPermission->setIsAssigned(false);
            $userPermission->setUpdatedAt(new \DateTime());
        }

        return $userPermission;
    }

    private function persistUserPermission($userId)
    {
        $em = $this->get('em');
        $user = $em->getReference('Newscoop\Entity\User', $userId);
        $userPermission = new Permissions();
        $userPermission->setUser($user);
        $em->persist($userPermission);
    }
}
