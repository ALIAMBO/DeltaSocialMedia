<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Message;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Create 5 users ──────────────────────────────────────────────
        $users = [
            [
                'name'  => 'Alice Rivera',
                'email' => 'alice@example.com',
                'bio'   => 'Photography enthusiast and coffee addict. ☕📷',
                'location' => 'New York, USA',
                'website'  => 'https://alicerivera.com',
            ],
            [
                'name'  => 'Bob Santos',
                'email' => 'bob@example.com',
                'bio'   => 'Full-stack developer. I build things for the web.',
                'location' => 'Manila, Philippines',
                'website'  => 'https://bobsantos.dev',
            ],
            [
                'name'  => 'Clara Mendez',
                'email' => 'clara@example.com',
                'bio'   => 'Traveler 🌍 | Foodie 🍜 | Writer ✍️',
                'location' => 'Barcelona, Spain',
                'website'  => null,
            ],
            [
                'name'  => 'Diego Reyes',
                'email' => 'diego@example.com',
                'bio'   => 'Fitness coach and marathon runner. 🏃‍♂️',
                'location' => 'Bogotá, Colombia',
                'website'  => 'https://diegofits.com',
            ],
            [
                'name'  => 'Ella Nguyen',
                'email' => 'ella@example.com',
                'bio'   => 'UI/UX designer crafting beautiful experiences. 🎨',
                'location' => 'Ho Chi Minh City, Vietnam',
                'website'  => 'https://elladesigns.io',
            ],
        ];

        $created = collect($users)->map(function ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
            ]);

            // Profile is auto-created by UserObserver, but we update it with richer data
            $user->profile()->update([
                'bio'      => $data['bio'],
                'location' => $data['location'],
                'website'  => $data['website'],
            ]);

            return $user;
        });

        [$alice, $bob, $clara, $diego, $ella] = $created;

        // ── 2. Follow relationships ────────────────────────────────────────
        $follows = [
            [$alice, $bob],
            [$alice, $clara],
            [$alice, $ella],
            [$bob,   $alice],
            [$bob,   $diego],
            [$bob,   $ella],
            [$clara, $alice],
            [$clara, $bob],
            [$diego, $alice],
            [$diego, $ella],
            [$ella,  $bob],
            [$ella,  $clara],
        ];

        foreach ($follows as [$follower, $following]) {
            Follow::create([
                'follower_id'  => $follower->id,
                'following_id' => $following->id,
            ]);
        }

        // ── 3. Posts ──────────────────────────────────────────────────────
        $posts = [
            // Alice
            Post::create(['user_id' => $alice->id, 'body' => "Just got my new camera lens! Can't wait to shoot golden hour this evening. 📷✨"]),
            Post::create(['user_id' => $alice->id, 'body' => "Morning coffee and a good book — the perfect Saturday. ☕📖 What are you all up to this weekend?"]),

            // Bob
            Post::create(['user_id' => $bob->id, 'body' => "Shipped a new feature today. After hours of debugging it turned out to be a missing semicolon. Classic. 😅"]),
            Post::create(['user_id' => $bob->id, 'body' => "Laravel 11 is genuinely a joy to work with. The slim application skeleton is so clean."]),

            // Clara
            Post::create(['user_id' => $clara->id, 'body' => "Barcelona sunsets hit different. 🌅 Sitting at my favorite rooftop spot right now."]),
            Post::create(['user_id' => $clara->id, 'body' => "Tried making homemade ramen from scratch today. It took 6 hours but it was absolutely worth it. 🍜"]),

            // Diego
            Post::create(['user_id' => $diego->id, 'body' => "Finished my first 42k marathon this morning! 🏃 Months of training finally paid off. Thank you everyone for the support!"]),
            Post::create(['user_id' => $diego->id, 'body' => "Rest day tip: active recovery is still recovery. Light walk, stretch, hydrate. Your muscles will thank you. 💪"]),

            // Ella
            Post::create(['user_id' => $ella->id, 'body' => "Just wrapped up a full redesign for a client's dashboard. Dark mode, clean typography, lots of whitespace. 🎨"]),
            Post::create(['user_id' => $ella->id, 'body' => "Hot take: consistency in spacing matters more than color choices. Fight me. 😄"]),
        ];

        // ── 4. Likes ──────────────────────────────────────────────────────
        $likePairs = [
            [$bob,   $posts[0]],   // Bob likes Alice's post 1
            [$clara, $posts[0]],   // Clara likes Alice's post 1
            [$ella,  $posts[0]],   // Ella likes Alice's post 1
            [$alice, $posts[1]],   // Alice likes Alice's post 2 (own — skip, handled below)
            [$diego, $posts[2]],   // Diego likes Bob's post 1
            [$alice, $posts[2]],   // Alice likes Bob's post 1
            [$ella,  $posts[3]],   // Ella likes Bob's post 2
            [$alice, $posts[4]],   // Alice likes Clara's post 1
            [$bob,   $posts[4]],   // Bob likes Clara's post 1
            [$diego, $posts[5]],   // Diego likes Clara's post 2
            [$alice, $posts[6]],   // Alice likes Diego's marathon post
            [$bob,   $posts[6]],
            [$clara, $posts[6]],
            [$ella,  $posts[6]],
            [$alice, $posts[7]],
            [$bob,   $posts[8]],   // Bob likes Ella's design post
            [$clara, $posts[8]],
            [$alice, $posts[9]],
            [$bob,   $posts[9]],
        ];

        foreach ($likePairs as [$user, $post]) {
            Like::firstOrCreate([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        }

        // ── 5. Comments ───────────────────────────────────────────────────
        $commentData = [
            [$bob,   $posts[0], "Great shot incoming, I can feel it! 📸"],
            [$ella,  $posts[0], "What lens did you get? I need to know! 👀"],
            [$clara, $posts[2], "Hahaha the missing semicolon struggle is so real 😂"],
            [$alice, $posts[3], "Agreed! The new directory structure is so much cleaner."],
            [$bob,   $posts[4], "Barcelona is on my bucket list. Looks amazing!"],
            [$alice, $posts[5], "Six hours for ramen — you're dedicated! Recipe please? 🙏"],
            [$ella,  $posts[5], "Homemade ramen is the best ramen!"],
            [$alice, $posts[6], "Congratulations!! That's such an incredible achievement! 🎉"],
            [$bob,   $posts[6], "Huge respect. 42k is no joke. Well done Diego!"],
            [$clara, $posts[6], "YOU DID IT!! So proud of you! 🏅"],
            [$alice, $posts[8], "The dark mode toggle is my favorite feature in any app 😌"],
            [$bob,   $posts[9], "Fully agree. Inconsistent spacing is the silent design killer."],
            [$clara, $posts[9], "This is so true and I'm quoting you on this. 😄"],
        ];

        foreach ($commentData as [$user, $post, $body]) {
            Comment::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'body'    => $body,
            ]);
        }

        // ── 6. Messages ───────────────────────────────────────────────────
        $conversations = [
            [$alice, $bob, [
                "Hey Bob! Loved your post about Laravel 11.",
                "Thanks Alice! Are you using it for any projects?",
                "Yes! Just started a new social media app actually 😄",
                "That's awesome, let me know if you need any help!",
            ]],
            [$ella, $bob, [
                "Hey, quick question — do you prefer Tailwind or plain CSS?",
                "Tailwind all the way. No contest for me.",
                "Same honestly. Especially with the new v4 syntax.",
                "Yes! The performance improvements are great too.",
            ]],
            [$clara, $alice, [
                "Alice! Your photography is stunning 😍",
                "Thank you so much Clara, that means a lot!",
                "Would love to collab on a travel + photography piece sometime!",
                "Oh wow, yes! I'd love that, let's plan something!",
            ]],
        ];

        foreach ($conversations as [$userA, $userB, $messages]) {
            foreach ($messages as $i => $body) {
                // Alternate sender/receiver to simulate a real back-and-forth
                $sender   = $i % 2 === 0 ? $userA : $userB;
                $receiver = $i % 2 === 0 ? $userB : $userA;

                Message::create([
                    'sender_id'   => $sender->id,
                    'receiver_id' => $receiver->id,
                    'body'        => $body,
                    'is_read'     => true,
                ]);
            }
        }

        $this->command->info('✅  5 users seeded with profiles, posts, likes, comments & messages.');
        $this->command->info('    Login with any user — password: password');
        $this->command->table(
            ['Name', 'Email', 'Password'],
            collect($users)->map(fn($u) => [$u['name'], $u['email'], 'password'])->toArray()
        );
    }
}
