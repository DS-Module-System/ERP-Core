/**
 * Module Loader Utility for automatically loading module scripts
 * Usage: Add new modules to the MODULES array when creating new module directories
 * Note: Each module must also be registered in importmap.php
 */

// Registry of all available modules with their importmap names
const MODULES = [
    { name: 'user', importName: 'module-user' }
    // Add new modules here: { name: 'moduleName', importName: 'module-moduleName' }
];

/**
 * Automatically load all registered module.js files
 */
export async function loadAllModules() {
    console.log('Loading modules:', MODULES.map(m => m.name));
    const loadPromises = MODULES.map(async (module) => {
        try {
            await import(module.importName);
            console.log(`✓ Loaded module: ${module.name}`);
            return { moduleName: module.name, success: true };
        } catch (error) {
            console.warn(`✗ Failed to load module ${module.name}:`, error);
            return { moduleName: module.name, success: false, error };
        }
    });
    
    const results = await Promise.allSettled(loadPromises);
    
    const successCount = results.filter(r => r.status === 'fulfilled' && r.value.success).length;
    console.log(`Module loading complete: ${successCount}/${MODULES.length} modules loaded successfully`);
    
    return results;
}

/**
 * Register a new module to be automatically loaded
 * @param {string} moduleName - The name of the module directory
 * @param {string} importName - The import name from importmap.php (optional, defaults to 'module-{moduleName}')
 */
export function registerModule(moduleName, importName = null) {
    const moduleImportName = importName || `module-${moduleName}`;
    const moduleExists = MODULES.some(m => m.name === moduleName);
    
    if (!moduleExists) {
        MODULES.push({ name: moduleName, importName: moduleImportName });
        console.log(`Registered new module: ${moduleName} (import: ${moduleImportName})`);
        console.warn(`Don't forget to add '${moduleImportName}' to importmap.php!`);
    }
}

/**
 * Get list of all registered modules
 */
export function getRegisteredModules() {
    return MODULES.map(m => ({ ...m }));
} 