<?php
// app/Traits/Notifiable.php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait Notifiable
{
    /**
     * Notificar um usuário específico
     */
    public function notifyUser($userId, $type, $title, $message, $options = [])
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'type' => $type, // ← Usa os tipos EXATOS do teu sistema
                'title' => $title,
                'message' => $message,
                'icon' => $options['icon'] ?? null,
                'priority' => $options['priority'] ?? 1,
                'action_url' => $options['action_url'] ?? null,
                'action_label' => $options['action_label'] ?? null,
                'data' => $options['data'] ?? null,
                'expires_at' => isset($options['expires_in_days'])
                    ? now()->addDays($options['expires_in_days'])
                    : now()->addDays(30),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar notificação: ' . $e->getMessage());
            return null;
        }
    }

    // ========== NOTIFICAÇÕES PARA ADMIN ==========

    /**
     * Notificar admins sobre NOVO USUÁRIO
     */
    public function notifyNewUserToAdmins($user)
    {
        return $this->notifyAdmins(
            'new_user_registered', // ← Tipo EXATO do teu sistema
            '👤 Novo Usuário Registrado',
            "{$user->name} ({$user->role}) acabou de se registrar",
            [
                'icon' => 'person_add',
                'priority' => 2,
                'action_url' => "/admin/users/{$user->id}",
                'data' => ['user_id' => $user->id]
            ]
        );
    }

    /**
     * Notificar admins sobre pesquisa pendente
     */
    public function notifySurveyPendingToAdmins($survey)
    {
        return $this->notifyAdmins(
            'survey_pending_review', // ← Tipo EXATO
            '📊 Pesquisa Pendente',
            "Uma nova pesquisa aguarda revisão",
            ['icon' => 'pending_actions', 'priority' => 2]
        );
    }

    /**
     * Notificar admins sobre solicitação de saque
     */
    public function notifyWithdrawalRequestToAdmins($withdrawal)
    {
        return $this->notifyAdmins(
            'withdrawal_requested', // ← Tipo EXATO
            '💰 Saque Solicitado',
            "Um usuário solicitou saque de {$withdrawal->amount} MZN",
            ['icon' => 'request_quote', 'priority' => 2]
        );
    }

    // ========== NOTIFICAÇÕES PARA STUDENT ==========

    /**
     * Notificar student sobre resposta de pesquisa
     */
    public function notifySurveyResponseToStudent($studentId, $survey)
    {
        return $this->notifyUser(
            $studentId,
            'survey_response', // ← Tipo EXATO
            '📝 Nova Resposta',
            "Sua pesquisa '{$survey->title}' recebeu uma nova resposta",
            [
                'icon' => 'assignment_turned_in',
                'priority' => 2,
                'action_url' => "/student/surveys/{$survey->id}/responses"
            ]
        );
    }

    /**
     * Notificar student sobre pesquisa aprovada
     */
    public function notifySurveyApprovedToStudent($studentId, $survey)
    {
        return $this->notifyUser(
            $studentId,
            'survey_approved', // ← Tipo EXATO
            '✅ Pesquisa Aprovada',
            "Sua pesquisa '{$survey->title}' foi aprovada",
            [
                'icon' => 'check_circle',
                'priority' => 3,
                'action_url' => "/student/surveys/{$survey->id}"
            ]
        );
    }

    /**
     * Notificar student sobre pagamento recebido
     */
    public function notifyPaymentToStudent($studentId, $amount)
    {
        return $this->notifyUser(
            $studentId,
            'payment_received', // ← Tipo EXATO
            '💰 Pagamento Recebido',
            "Você recebeu {$amount} MZN",
            ['icon' => 'payments', 'priority' => 2]
        );
    }

    // ========== NOTIFICAÇÕES PARA PARTICIPANT ==========

    /**
     * Notificar participant sobre nova pesquisa disponível
     */
    public function notifyNewSurveyToParticipant($participantId, $survey)
    {
        return $this->notifyUser(
            $participantId,
            'survey_available', // ← Tipo EXATO
            '📋 Nova Pesquisa Disponível',
            "{$survey->title} - Ganhe {$survey->reward} MZN",
            [
                'icon' => 'assignment',
                'priority' => 2,
                'action_url' => "/participant/surveys/{$survey->id}"
            ]
        );
    }

    /**
     * Notificar participant sobre resposta completada
     */
    public function notifyResponseCompletedToParticipant($participantId, $survey)
    {
        return $this->notifyUser(
            $participantId,
            'response_completed', // ← Tipo EXATO
            '✅ Resposta Enviada',
            "Sua resposta para '{$survey->title}' foi enviada com sucesso",
            ['icon' => 'done_all', 'priority' => 1]
        );
    }

    /**
     * Notificar participant sobre crédito de pagamento
     */
    public function notifyPaymentCreditedToParticipant($participantId, $amount)
    {
        return $this->notifyUser(
            $participantId,
            'payment_credited', // ← Tipo EXATO
            '💰 Crédito Recebido',
            "{$amount} MZN foram creditados na sua conta",
            ['icon' => 'attach_money', 'priority' => 2]
        );
    }

    /**
     * Notificar participant sobre bônus recebido
     */
    public function notifyBonusToParticipant($participantId, $amount)
    {
        return $this->notifyUser(
            $participantId,
            'bonus_received', // ← Tipo EXATO
            '🎁 Bônus Recebido',
            "Você recebeu {$amount} MZN de bônus",
            ['icon' => 'card_giftcard', 'priority' => 2]
        );
    }

    // ========== MÉTODOS GENÉRICOS (USAM OS TIPOS EXATOS) ==========

    /**
     * Notificar todos os admins (genérico)
     */
    public function notifyAdmins($type, $title, $message, $options = [])
    {
        $admins = User::where('role', 'admin')->get();
        $count = 0;
        foreach ($admins as $admin) {
            $result = $this->notifyUser($admin->id, $type, $title, $message, $options);
            if ($result) $count++;
        }
        return $count;
    }

    /**
     * Notificar todos de um perfil específico
     */
    public function notifyByRole($role, $type, $title, $message, $options = [])
    {
        $users = User::where('role', $role)->get();
        $count = 0;
        foreach ($users as $user) {
            $result = $this->notifyUser($user->id, $type, $title, $message, $options);
            if ($result) $count++;
        }
        return $count;
    }
}
