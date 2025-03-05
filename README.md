# Laravel CRUD Generator Package

![Laravel CRUD Generator](https://img.shields.io/badge/Laravel-CRUD_Generator-brightgreen)
![License](https://img.shields.io/badge/license-MIT-blue)

A powerful CLI tool to generate complete CRUD operations with translations, permissions, and modular architecture support.

## 📦 Features

- **One-Command CRUD Generation**
- **Multilingual Support** (Model translations + migration translations)
- **Modular Architecture** (Works with [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules))
- **Optional Components**:
  - Soft Delete & Disabled Status
  - API Controllers
  - Permissions System
  - Factories & Seeders
  - Form Requests
  - Blade Views
- **Custom Stub Support**
- **Automatic Route Generation**
- **Permission Seeding**

## 🚀 Installation

1. Install via Composer:
```bash
composer require arrahmouni/crud-generator
```

2. Publish configuration and stubs:
```bash
php artisan vendor:publish --provider="CrudGeneratorServiceProvider" --tag=config
php artisan vendor:publish --provider="CrudGeneratorServiceProvider" --tag=stubs
```

## 🛠 Configuration

Edit `config/crud.php` to customize:
```php
return [
    'stub_path' => resource_path('stubs/'), // Custom stub path
    // Add other configuration parameters
];
```

## 🎯 Basic Usage

Generate a full CRUD structure with interactive prompts:
```bash
php artisan create:crud ModelName ModuleName
```

**Example** - Create a Product CRUD in Catalog module:
```bash
php artisan create:crud Product Catalog
```

## 🔧 Command Workflow

You'll be asked about these options during generation:
- [ ] Soft Delete
- [ ] Disabled Status
- [ ] Translations
- [ ] Migrations
- [ ] Factories
- [ ] Seeders
- [ ] Requests
- [ ] Controllers (Web + API)
- [ ] Permissions
- [ ] Views

![Command Demo](https://via.placeholder.com/800x400.png?text=CRUD+Generator+Demo)

## 📂 Generated Structure

```
ModuleName/
├── Models/
│   └── ModelName.php
├── Resources/
│   └── views/index.blade.php
│   └── views/create.blade.php
│   └── views/update.blade.php
├── Http/
│   ├── Controllers/Admin
│   ├── Controllers/Api
│   └── Requests/
├── Resources/
│   ├── ModelResource.php
├── Database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
└── Routes/
    └── web.php (auto-updated)
```

## 🔄 Post-Creation Steps

1. Add translations to language files:
   - `admin::dashboard`
   - `admin::cruds`
   - `admin::datatables`

2. Configure permissions in Permission module config

3. Sync permissions:
```bash
php artisan module:seed Permission
```

4. Run migrations:
```bash
php artisan module:migrate ModuleName
```

## 🛡 Service Provider

The `CrudGeneratorServiceProvider` handles:
- Command registration
- Stub path configuration
- Package asset publishing
- Configuration management

## 📜 License

This package is open-source software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

**Happy CRUD Generation!** 🚀
