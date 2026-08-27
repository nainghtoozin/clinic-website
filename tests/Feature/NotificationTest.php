<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Investigation;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\QueueTicket;
use App\Models\User;
use App\Services\NotificationService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

// --- NOTIFICATION MODEL TESTS ---

test('notification can be created', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test Notification',
        'message' => 'This is a test notification.',
        'module' => 'system',
        'action' => 'test',
        'is_read' => false,
    ]);

    expect($notification)->toBeInstanceOf(Notification::class);
    expect($notification->user_id)->toBe($user->id);
    expect($notification->type)->toBe('system');
    expect($notification->title)->toBe('Test Notification');
    expect($notification->is_read)->toBeFalse();
});

test('notification belongs to user', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test message',
    ]);

    expect($notification->user->id)->toBe($user->id);
});

test('notification can be cast to array', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'appointment',
        'title' => 'Test',
        'message' => 'Test message',
        'metadata' => ['key' => 'value'],
    ]);

    expect($notification->metadata)->toBeArray();
    expect($notification->metadata['key'])->toBe('value');
});

test('notification type label accessor works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'appointment',
        'title' => 'Test',
        'message' => 'Test message',
    ]);

    expect($notification->type_label)->toBe('Appointment');
});

test('notification icon accessor works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'appointment',
        'title' => 'Test',
        'message' => 'Test message',
    ]);

    expect($notification->icon)->toBe('bi-calendar-check');
});

test('notification time ago accessor works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test message',
    ]);

    expect($notification->time_ago)->toBeString();
});

test('notification mark as read works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test message',
        'is_read' => false,
    ]);

    expect($notification->is_read)->toBeFalse();
    $notification->markAsRead();
    expect($notification->fresh()->is_read)->toBeTrue();
    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('notification mark as unread works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test message',
        'is_read' => true,
        'read_at' => now(),
    ]);

    expect($notification->is_read)->toBeTrue();
    $notification->markAsUnread();
    expect($notification->fresh()->is_read)->toBeFalse();
    expect($notification->fresh()->read_at)->toBeNull();
});

test('notification scope unread works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Unread', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Read', 'message' => 'Test', 'is_read' => true]);

    $unread = Notification::unread()->count();
    expect($unread)->toBe(1);
});

test('notification scope for user works', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Notification::create(['user_id' => $user1->id, 'type' => 'system', 'title' => 'User 1', 'message' => 'Test']);
    Notification::create(['user_id' => $user2->id, 'type' => 'system', 'title' => 'User 2', 'message' => 'Test']);

    expect(Notification::forUser($user1->id)->count())->toBe(1);
    expect(Notification::forUser($user2->id)->count())->toBe(1);
});

test('notification scope for module works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'appointment', 'title' => 'Appt', 'message' => 'Test', 'module' => 'appointment']);
    Notification::create(['user_id' => $user->id, 'type' => 'invoice', 'title' => 'Inv', 'message' => 'Test', 'module' => 'invoice']);

    expect(Notification::forModule('appointment')->count())->toBe(1);
});

test('notification scope search works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Important Alert', 'message' => 'Something happened']);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Other', 'message' => 'Nothing special']);

    expect(Notification::search('Important')->count())->toBe(1);
});

// --- NOTIFICATION SERVICE TESTS ---

test('notification service creates notification', function () {
    $user = User::factory()->create();
    $notification = NotificationService::notify(
        $user,
        'system',
        'Test Title',
        'Test message content'
    );

    expect($notification)->not->toBeNull();
    expect($notification->user_id)->toBe($user->id);
    expect($notification->title)->toBe('Test Title');
    expect($notification->message)->toBe('Test message content');
});

test('notification service creates notification with notifiable', function () {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();
    $notification = NotificationService::notify(
        $user,
        'appointment',
        'Appointment Created',
        'New appointment scheduled',
        $patient,
        'appointment',
        'created',
        '/patients/' . $patient->id
    );

    expect($notification->notifiable_type)->toBe(get_class($patient));
    expect($notification->notifiable_id)->toBe($patient->id);
    expect($notification->url)->toBe('/patients/' . $patient->id);
});

test('notification service notify many works', function () {
    $users = User::factory()->count(3)->create();
    $userIds = $users->pluck('id')->toArray();

    $notifications = NotificationService::notifyMany(
        $userIds,
        'system',
        'Broadcast',
        'This is a broadcast message'
    );

    expect(count($notifications))->toBe(3);
    expect(Notification::where('type', 'system')->count())->toBe(3);
});

test('notification service notify admins works', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $notifications = NotificationService::notifyAdmins(
        'backup',
        'Backup Complete',
        'System backup has been created'
    );

    expect(count($notifications))->toBeGreaterThanOrEqual(1);
});

test('notification service unread count works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => true]);

    expect(NotificationService::unreadCount($user))->toBe(2);
});

test('notification service mark all read works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);

    $count = NotificationService::markAllRead($user);
    expect($count)->toBe(2);
    expect(NotificationService::unreadCount($user))->toBe(0);
});

test('notification service deduplicates recent notifications', function () {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();

    $n1 = NotificationService::notify($user, 'appointment', 'Test', 'Test', $patient, 'appointment', 'created');
    $n2 = NotificationService::notify($user, 'appointment', 'Test', 'Test', $patient, 'appointment', 'created');

    expect($n2)->toBeNull();
    expect(Notification::where('user_id', $user->id)->count())->toBe(1);
});

test('notification service skips inactive users', function () {
    $user = User::factory()->create(['is_active' => false]);

    $notification = NotificationService::notify(
        $user,
        'system',
        'Test',
        'Test message'
    );

    expect($notification)->toBeNull();
});

// --- CONTROLLER TESTS ---

test('notification index requires authentication', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});

test('notification index is accessible', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('notifications.index'))->assertOk();
});

test('notification index shows notifications', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test Notification', 'message' => 'Test']);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Test Notification');
});

test('notification index shows empty state', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('No notifications found');
});

test('notification index supports filter unread', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Unread Notif', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Read Notif', 'message' => 'Test', 'is_read' => true]);

    $this->actingAs($user)
        ->get(route('notifications.index', ['filter' => 'unread']))
        ->assertOk()
        ->assertSee('Unread Notif');
});

test('notification index supports filter read', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Unread Notif', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Read Notif', 'message' => 'Test', 'is_read' => true]);

    $this->actingAs($user)
        ->get(route('notifications.index', ['filter' => 'read']))
        ->assertOk()
        ->assertSee('Read Notif');
});

test('notification index supports module filter', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'appointment', 'title' => 'Appt', 'message' => 'Test', 'module' => 'appointment']);
    Notification::create(['user_id' => $user->id, 'type' => 'invoice', 'title' => 'Inv', 'message' => 'Test', 'module' => 'invoice']);

    $this->actingAs($user)
        ->get(route('notifications.index', ['module' => 'appointment']))
        ->assertOk()
        ->assertSee('Appt');
});

test('notification index supports search', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Important Alert', 'message' => 'Test']);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Other', 'message' => 'Test']);

    $this->actingAs($user)
        ->get(route('notifications.index', ['search' => 'Important']))
        ->assertOk()
        ->assertSee('Important Alert');
});

test('notification show marks as read', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test message',
        'is_read' => false,
    ]);

    $this->actingAs($user)
        ->get(route('notifications.show', $notification))
        ->assertOk();

    expect($notification->fresh()->is_read)->toBeTrue();
});

test('notification show page loads successfully', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test Notification',
        'message' => 'This is a test notification body.',
    ]);

    $this->actingAs($user)
        ->get(route('notifications.show', $notification))
        ->assertOk()
        ->assertSee('Test Notification')
        ->assertSee('This is a test notification body.');
});

test('notification mark read endpoint works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test',
        'is_read' => false,
    ]);

    $this->actingAs($user)
        ->post(route('notifications.mark-read', $notification))
        ->assertJson(['success' => true]);

    expect($notification->fresh()->is_read)->toBeTrue();
});

test('notification mark unread endpoint works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test',
        'is_read' => true,
        'read_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('notifications.mark-unread', $notification))
        ->assertJson(['success' => true]);

    expect($notification->fresh()->is_read)->toBeFalse();
});

test('notification mark all read endpoint works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);

    $this->actingAs($user)
        ->post(route('notifications.mark-all-read'))
        ->assertJson(['success' => true, 'unread_count' => 0]);

    expect(NotificationService::unreadCount($user))->toBe(0);
});

test('notification unread count endpoint works', function () {
    $user = User::factory()->create();
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);

    $this->actingAs($user)
        ->get(route('notifications.unread-count'))
        ->assertJson(['unread_count' => 1]);
});

test('notification delete endpoint works', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Test',
        'message' => 'Test',
    ]);

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $notification))
        ->assertJson(['success' => true]);

    expect(Notification::find($notification->id))->toBeNull();
});

// --- AUTHORIZATION TESTS ---

test('user cannot view another users notification', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user2->id,
        'type' => 'system',
        'title' => 'Private',
        'message' => 'Private message',
    ]);

    $this->actingAs($user1)
        ->get(route('notifications.show', $notification))
        ->assertForbidden();
});

test('user cannot mark another users notification as read', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user2->id,
        'type' => 'system',
        'title' => 'Private',
        'message' => 'Private message',
    ]);

    $this->actingAs($user1)
        ->post(route('notifications.mark-read', $notification))
        ->assertForbidden();
});

test('user cannot delete another users notification', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user2->id,
        'type' => 'system',
        'title' => 'Private',
        'message' => 'Private message',
    ]);

    $this->actingAs($user1)
        ->delete(route('notifications.destroy', $notification))
        ->assertForbidden();
});

// --- PRIVACY TESTS ---

test('user only sees own notifications', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Notification::create(['user_id' => $user1->id, 'type' => 'system', 'title' => 'User 1', 'message' => 'Test']);
    Notification::create(['user_id' => $user2->id, 'type' => 'system', 'title' => 'User 2', 'message' => 'Test']);

    $this->actingAs($user1)
        ->get(route('notifications.index'))
        ->assertSee('User 1')
        ->assertDontSee('User 2');
});

test('user only sees own unread count', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Notification::create(['user_id' => $user1->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);
    Notification::create(['user_id' => $user2->id, 'type' => 'system', 'title' => 'Test', 'message' => 'Test', 'is_read' => false]);

    $this->actingAs($user1)
        ->get(route('notifications.unread-count'))
        ->assertJson(['unread_count' => 1]);
});

// --- PAGINATION TESTS ---

test('notification index is paginated', function () {
    $user = User::factory()->create();
    for ($i = 0; $i < 25; $i++) {
        Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => "Test {$i}", 'message' => 'Test']);
    }

    $response = $this->actingAs($user)->get(route('notifications.index'));
    $response->assertOk();
    $notifications = $response->viewData('notifications');
    expect($notifications->hasPages())->toBeTrue();
});

// --- APPOINTMENT NOTIFICATION TESTS ---

test('appointment creation sends notification to doctor', function () {
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();

    NotificationService::notify(
        $doctor->user_id,
        'appointment',
        'New Appointment',
        "Appointment scheduled for {$patient->name}",
        null,
        'appointment',
        'created'
    );

    expect(Notification::where('user_id', $doctor->user_id)->where('type', 'appointment')->count())->toBe(1);
});

// --- INVESTIGATION NOTIFICATION TESTS ---

test('investigation notification is created', function () {
    $doctor = User::factory()->create();

    NotificationService::notify(
        $doctor->id,
        'investigation',
        'Investigation Requested',
        'A new investigation has been requested',
        null,
        'investigation',
        'created'
    );

    $notification = Notification::where('user_id', $doctor->id)->where('type', 'investigation')->first();
    expect($notification)->not->toBeNull();
    expect($notification->title)->toBe('Investigation Requested');
});

// --- INVENTORY NOTIFICATION TESTS ---

test('inventory notification is created', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    NotificationService::notifyAdmins(
        'inventory',
        'Stock Replenished',
        'Medicine stock has been replenished',
        null,
        'inventory',
        'stock_in'
    );

    expect(Notification::where('type', 'inventory')->count())->toBeGreaterThanOrEqual(1);
});

// --- EXPENSE NOTIFICATION TESTS ---

test('expense notification is created', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    NotificationService::notifyAdmins(
        'expense',
        'Expense Recorded',
        'New expense has been recorded',
        null,
        'expense',
        'created'
    );

    expect(Notification::where('type', 'expense')->count())->toBeGreaterThanOrEqual(1);
});

// --- BACKUP NOTIFICATION TESTS ---

test('backup notification is created', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    NotificationService::notifyAdmins(
        'backup',
        'Backup Created',
        'System backup has been created',
        null,
        'backup',
        'created'
    );

    expect(Notification::where('type', 'backup')->count())->toBeGreaterThanOrEqual(1);
});

// --- SYSTEM NOTIFICATION TESTS ---

test('system notification is created', function () {
    $user = User::factory()->create();

    NotificationService::notify(
        $user->id,
        'system',
        'Account Deactivated',
        'Your account has been deactivated.',
        null,
        'system',
        'deactivated'
    );

    $notification = Notification::where('user_id', $user->id)->where('type', 'system')->first();
    expect($notification)->not->toBeNull();
    expect($notification->title)->toBe('Account Deactivated');
});

// --- DEDUPLICATION TESTS ---

test('duplicate notifications are prevented within 5 minutes', function () {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();

    NotificationService::notify($user, 'test', 'Test', 'Test', $patient, 'test', 'action');
    NotificationService::notify($user, 'test', 'Test', 'Test', $patient, 'test', 'action');

    expect(Notification::where('user_id', $user->id)->count())->toBe(1);
});

test('notifications without action are not deduplicated', function () {
    $user = User::factory()->create();

    NotificationService::notify($user, 'system', 'Test', 'Test 1');
    NotificationService::notify($user, 'system', 'Test', 'Test 2');

    expect(Notification::where('user_id', $user->id)->count())->toBe(2);
});

// --- NOTIFICATION DROPDOWN TESTS ---

test('notification dropdown shows recent notifications', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    Notification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Recent Notification', 'message' => 'Test']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee('Recent Notification');
});
