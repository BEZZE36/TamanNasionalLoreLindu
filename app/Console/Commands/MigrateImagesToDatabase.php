<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\DestinationImage;
use App\Models\Fauna;
use App\Models\Flora;
use App\Models\Gallery;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'images:migrate {--backup : Create backup before migration} {--cleanup : Delete files after successful migration}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate all images from file system to database storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting image migration to database...');
        
        $backupPath = storage_path('app/image_backup_' . date('Y-m-d_His'));
        
        if ($this->option('backup')) {
            $this->info('Creating backup folder: ' . $backupPath);
            File::makeDirectory($backupPath, 0755, true);
        }

        // Migrate Flora images
        $this->migrateFlora($backupPath);
        
        // Migrate Fauna images
        $this->migrateFauna($backupPath);
        
        // Migrate Gallery images
        $this->migrateGallery($backupPath);
        
        // Migrate Banner images
        $this->migrateBanner($backupPath);
        
        // Migrate Destination images
        $this->migrateDestinationImages($backupPath);
        
        $this->newLine();
        $this->info('✅ Image migration completed successfully!');
        
        if ($this->option('backup')) {
            $this->info('📁 Backup saved to: ' . $backupPath);
        }
        
        if ($this->option('cleanup')) {
            $this->info('Cleaning up old image files...');
            $this->cleanupFiles();
        }
        
        return 0;
    }

    protected function migrateFlora(string $backupPath): void
    {
        $this->info('Migrating Flora images...');
        $flora = Flora::whereNotNull('image_path')->whereNull('image_data')->get();
        
        $bar = $this->output->createProgressBar($flora->count());
        $bar->start();

        foreach ($flora as $item) {
            $imageData = ImageService::storeFromPath($item->image_path);
            
            if ($imageData) {
                // Backup if needed
                if ($this->option('backup')) {
                    $this->backupFile($item->image_path, $backupPath . '/flora');
                }
                
                $item->update([
                    'image_data' => $imageData['data'],
                    'image_mime' => $imageData['mime'],
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  → Migrated {$flora->count()} flora images");
    }

    protected function migrateFauna(string $backupPath): void
    {
        $this->info('Migrating Fauna images...');
        $fauna = Fauna::whereNotNull('image_path')->whereNull('image_data')->get();
        
        $bar = $this->output->createProgressBar($fauna->count());
        $bar->start();

        foreach ($fauna as $item) {
            $imageData = ImageService::storeFromPath($item->image_path);
            
            if ($imageData) {
                if ($this->option('backup')) {
                    $this->backupFile($item->image_path, $backupPath . '/fauna');
                }
                
                $item->update([
                    'image_data' => $imageData['data'],
                    'image_mime' => $imageData['mime'],
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  → Migrated {$fauna->count()} fauna images");
    }

    protected function migrateGallery(string $backupPath): void
    {
        $this->info('Migrating Gallery images...');
        $galleries = Gallery::where('type', 'image')
            ->whereNotNull('image_path')
            ->whereNull('image_data')
            ->get();
        
        $bar = $this->output->createProgressBar($galleries->count());
        $bar->start();

        foreach ($galleries as $item) {
            $imageData = ImageService::storeFromPath($item->image_path);
            
            if ($imageData) {
                if ($this->option('backup')) {
                    $this->backupFile($item->image_path, $backupPath . '/gallery');
                }
                
                $item->update([
                    'image_data' => $imageData['data'],
                    'image_mime' => $imageData['mime'],
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  → Migrated {$galleries->count()} gallery images");
    }

    protected function migrateBanner(string $backupPath): void
    {
        $this->info('Migrating Banner images...');
        $banners = Banner::whereNotNull('image_path')->whereNull('image_data')->get();
        
        $bar = $this->output->createProgressBar($banners->count());
        $bar->start();

        foreach ($banners as $banner) {
            // Desktop image
            if ($banner->image_path) {
                $imageData = ImageService::storeFromPath($banner->image_path);
                
                if ($imageData) {
                    if ($this->option('backup')) {
                        $this->backupFile($banner->image_path, $backupPath . '/banners');
                    }
                    
                    $banner->image_data = $imageData['data'];
                    $banner->image_mime = $imageData['mime'];
                }
            }
            
            // Mobile image
            if ($banner->mobile_image_path) {
                $mobileData = ImageService::storeFromPath($banner->mobile_image_path);
                
                if ($mobileData) {
                    if ($this->option('backup')) {
                        $this->backupFile($banner->mobile_image_path, $backupPath . '/banners');
                    }
                    
                    $banner->mobile_image_data = $mobileData['data'];
                    $banner->mobile_image_mime = $mobileData['mime'];
                }
            }
            
            $banner->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  → Migrated {$banners->count()} banner images");
    }

    protected function migrateDestinationImages(string $backupPath): void
    {
        $this->info('Migrating Destination images...');
        $images = DestinationImage::whereNotNull('image_path')->whereNull('image_data')->get();
        
        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $image) {
            $imageData = ImageService::storeFromPath($image->image_path);
            
            if ($imageData) {
                if ($this->option('backup')) {
                    $this->backupFile($image->image_path, $backupPath . '/destinations');
                }
                
                $image->update([
                    'image_data' => $imageData['data'],
                    'image_mime' => $imageData['mime'],
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  → Migrated {$images->count()} destination images");
    }

    protected function backupFile(string $path, string $targetDir): void
    {
        // Try storage first
        if (Storage::disk('public')->exists($path)) {
            $contents = Storage::disk('public')->get($path);
            $filename = basename($path);
            
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            
            File::put($targetDir . '/' . $filename, $contents);
            return;
        }
        
        // Try public/assets
        $assetPath = public_path('assets/' . $path);
        if (file_exists($assetPath)) {
            $filename = basename($path);
            
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            
            File::copy($assetPath, $targetDir . '/' . $filename);
        }
    }

    protected function cleanupFiles(): void
    {
        // Delete storage folders
        $folders = ['flora', 'fauna', 'gallery', 'banners', 'destinations', 'uploads', 'reviews'];
        
        foreach ($folders as $folder) {
            if (Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->deleteDirectory($folder);
                $this->line("  → Deleted storage/{$folder}");
            }
        }
        
        $this->info('✅ Cleanup completed');
    }
}
