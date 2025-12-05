# Kiến trúc Layout System - Jankx WooCommerce

## Tổng quan kiến trúc

Hệ thống được thiết kế theo các nguyên tắc SOLID và sử dụng nhiều Design Patterns để đảm bảo tính mở rộng, bảo trì và hiệu năng cao.

## Design Patterns được sử dụng

### 1. Registry Pattern
**Mục đích**: Quản lý tập trung tất cả layouts đã đăng ký

**Implementation**: `LayoutManager`
- Lưu trữ tất cả layouts trong một registry
- Cung cấp API để register/unregister/get layouts
- Group layouts theo type
- Quản lý default layouts

**Lợi ích**:
- Centralized management
- Dễ dàng query layouts
- Tránh duplicate registration
- Type safety

### 2. Singleton Pattern
**Mục đích**: Đảm bảo chỉ có một instance của managers

**Implementation**: `LayoutManager`, `CssManager`, `SettingsManager`, `LayoutBootstrap`
- Private constructor
- Static getInstance() method
- Prevent cloning và unserialization

**Lợi ích**:
- Global access point
- Consistent state
- Resource efficiency
- Prevent multiple instances

### 3. Template Method Pattern
**Mục đích**: Định nghĩa skeleton của algorithm, cho phép subclass override specific steps

**Implementation**: `AbstractLayout` và các abstract layout classes
- Abstract class định nghĩa template method (render)
- Hook methods cho subclasses override (renderGallery, renderSummary, etc.)
- Common logic trong base class

**Lợi ích**:
- Code reuse
- Consistent structure
- Easy to extend
- Enforce contract

### 4. Strategy Pattern
**Mục đích**: Cho phép swap algorithms (layouts) dynamically

**Implementation**: Layout switching via filters
- Layouts implement same interface
- Can be swapped at runtime
- Filter-based selection

**Lợi ích**:
- Flexible layout selection
- A/B testing support
- Conditional layouts
- Runtime configuration

### 5. Facade Pattern
**Mục đích**: Provide simplified interface to complex subsystem

**Implementation**: `LayoutBootstrap`
- Unified interface cho layout system
- Hides complexity of managers
- Simple API for common tasks

**Lợi ích**:
- Simplified usage
- Decoupling
- Easy integration

## SOLID Principles

### Single Responsibility Principle (SRP)
✅ Mỗi class có một responsibility duy nhất:
- `LayoutManager`: Quản lý layouts
- `CssManager`: Quản lý CSS compilation và injection
- `SettingsManager`: Quản lý settings
- Each layout class: Render một loại layout cụ thể

### Open/Closed Principle (OCP)
✅ Open for extension, closed for modification:
- Interfaces và abstract classes stable
- New layouts extend abstracts (không modify base)
- Hooks và filters cho extensibility
- Plugin/theme có thể extend mà không touch core

### Liskov Substitution Principle (LSP)
✅ Subclasses có thể replace base classes:
- All layouts implement `LayoutInterface`
- Substitutable trong `LayoutManager`
- Methods maintain contracts
- Consistent behavior

### Interface Segregation Principle (ISP)
✅ Specific interfaces thay vì fat interfaces:
- Base `LayoutInterface` với common methods
- Specific interfaces cho mỗi layout type
- Clients chỉ depend vào methods cần thiết
- Không force implement unused methods

### Dependency Inversion Principle (DIP)
✅ Depend on abstractions, not concretions:
- Code depends on interfaces
- Concrete implementations injected
- Loose coupling
- Easy testing và mocking

## Dependency Flow

```
┌─────────────────────────────────────────┐
│         WordPress/WooCommerce           │
└──────────────────┬──────────────────────┘
                   │
                   ↓
┌──────────────────────────────────────────┐
│         LayoutBootstrap                  │
│  (Facade - Entry Point)                  │
└──────┬────────────┬────────────┬─────────┘
       │            │            │
       ↓            ↓            ↓
┌──────────┐  ┌──────────┐  ┌──────────┐
│ Layout   │  │   CSS    │  │ Settings │
│ Manager  │  │ Manager  │  │ Manager  │
└────┬─────┘  └────┬─────┘  └────┬─────┘
     │             │              │
     ↓             ↓              ↓
┌─────────────────────────────────────────┐
│           Concrete Layouts              │
│  (Extend Abstract Classes)              │
└─────────────────────────────────────────┘
```

## Class Hierarchy

```
LayoutInterface (Contract)
    ↑
AbstractLayout (Base Implementation)
    ↑
[Type-Specific Interface]
    ↑
[Type-Specific Abstract]
    ↑
[Concrete Layout]

Ví dụ:
LayoutInterface
    ↑
AbstractLayout
    ↑
ProductDetailLayoutInterface
    ↑
AbstractProductDetailLayout
    ↑
DefaultProductDetailLayout
```

## Data Flow

### 1. Layout Registration Flow
```
Theme/Plugin
    → register($layout)
    → LayoutManager::register()
    → Store in registry
    → Group by type
    → Set default if first
    → Fire hook
```

### 2. Layout Rendering Flow
```
Page Load
    → Determine context (is_product?, is_shop?)
    → Get layout type
    → LayoutManager::getDefault($type)
    → Apply filters (allow override)
    → Get layout settings
    → Layout::render($data)
    → Return HTML
```

### 3. CSS Injection Flow
```
wp_enqueue_scripts
    → Get current layout
    → Get layout settings
    → Compile common CSS (cached)
    → Generate dynamic CSS
    → Combine CSS
    → CssManager::inject()
    → wp_head outputs <style>
```

## Performance Optimizations

### 1. CSS Strategy
- ✅ Inline CSS only (no external requests)
- ✅ SCSS compiled và cached
- ✅ Minification in production
- ✅ Only inject needed CSS
- ✅ Track injected để avoid duplicates

### 2. Caching Layers
```
┌─────────────────────────────────────┐
│  SCSS File → Compiler → CSS Output │
│              ↓ Cache                │
│         Transients                  │
│    (invalidate on file change)      │
└─────────────────────────────────────┘
```

### 3. Lazy Loading
- Layouts loaded only when registered
- CSS compiled only when needed
- Settings loaded on-demand

## Extensibility Points

### 1. Hooks (Actions)
- `jankx_woocommerce_register_default_layouts`
- `jankx_woocommerce_register_layouts`
- `jankx_woocommerce_layout_registered`
- `jankx_woocommerce_default_layout_changed`

### 2. Filters
- `jankx_woocommerce_current_layout`
- `jankx_woocommerce_default_layout_id`
- `jankx_woocommerce_compiled_css`
- `jankx_woocommerce_scss_import_paths`
- `jankx_woocommerce_allow_layout_override`

### 3. Override Points
- Template files (themes can override)
- SCSS files (themes can provide own)
- CSS generators (custom CSS logic)
- Settings validation (custom validation)

## Security Considerations

### 1. Input Validation
- All settings validated before save
- Sanitization functions used
- Type checking
- Nonce verification

### 2. Output Escaping
- HTML escaped with esc_html()
- Attributes escaped with esc_attr()
- URLs escaped with esc_url()
- CSS escaped appropriately

### 3. Capability Checks
- Admin functions check capabilities
- Settings changes require proper permissions
- Layout registration from trusted sources only

## Testing Strategy

### 1. Unit Tests
- Test individual classes
- Mock dependencies
- Test edge cases
- Test SOLID compliance

### 2. Integration Tests
- Test interaction between managers
- Test layout registration flow
- Test CSS compilation
- Test settings persistence

### 3. Performance Tests
- Measure CSS compilation time
- Measure rendering time
- Check memory usage
- Profile bottlenecks

## Migration Path

### From old system to new
1. Keep backward compatibility hooks
2. Gradual migration of layouts
3. Deprecation notices
4. Documentation for migration

## Future Enhancements

### Planned Features
1. Visual layout builder (drag & drop)
2. Layout preview in admin
3. Import/export layouts
4. Layout marketplace
5. Advanced CSS optimization (critical CSS)
6. Layout versioning
7. Layout analytics

### Scalability Considerations
- Support cho millions of products
- Multi-site support
- CDN integration
- Advanced caching strategies

## Best Practices for Developers

### DO ✅
- Extend abstract classes
- Use interfaces for type hints
- Follow naming conventions
- Document public APIs
- Validate inputs
- Escape outputs
- Use hooks và filters
- Write tests

### DON'T ❌
- Modify core files
- Access private properties
- Bypass validation
- Hard-code values
- Ignore security
- Skip documentation
- Create tight coupling

## Code Quality Metrics

### Maintainability
- Clear separation of concerns
- Low coupling
- High cohesion
- Well documented
- Consistent style

### Testability
- Dependency injection
- Interface-based design
- Mockable dependencies
- Clear contracts

### Performance
- Efficient caching
- Lazy loading
- Minimal queries
- Optimized CSS

### Security
- Input validation
- Output escaping
- Capability checks
- Nonce verification

---

Kiến trúc này đảm bảo hệ thống:
- ✅ Dễ maintain và extend
- ✅ High performance (Core Web Vitals)
- ✅ Secure
- ✅ Testable
- ✅ Well documented
- ✅ Production-ready

