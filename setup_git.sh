#!/bin/bash

# =====================================================
# SCRIPT DE CONFIGURACIÓN DE GIT PARA SISTEMA DE PAGOS
# =====================================================

echo "🚀 Configurando repositorio Git para Sistema de Pagos..."

# Verificar si Git está instalado
if ! command -v git &> /dev/null; then
    echo "❌ Error: Git no está instalado"
    echo "Por favor instala Git primero:"
    echo "  - Windows: https://git-scm.com/download/win"
    echo "  - macOS: brew install git"
    echo "  - Linux: sudo apt-get install git"
    exit 1
fi

# Inicializar repositorio Git
echo "📁 Inicializando repositorio Git..."
git init

# Configurar .gitignore
echo "📝 Configurando .gitignore..."
if [ -f ".gitignore" ]; then
    echo "✅ .gitignore ya existe"
else
    echo "❌ Error: .gitignore no encontrado"
    exit 1
fi

# Agregar archivos al repositorio
echo "📦 Agregando archivos al repositorio..."
git add .

# Hacer commit inicial
echo "💾 Haciendo commit inicial..."
git commit -m "🎉 Commit inicial: Sistema de Pagos GloboCity v1.0.0

✅ Sistema completo de gestión de pagos
✅ APIs RESTful funcionales
✅ Validaciones implementadas
✅ Interfaz responsiva
✅ Scripts de verificación
✅ Documentación completa

Archivos incluidos:
- Pago_fac.html (módulo principal)
- api/get_fact_pago.php
- api/registrar_pago.php
- verificar_sistema_pagos.php
- test_apis.php
- probar_apis_directo.php
- limpiar_todo_completamente.sql
- README.md (documentación)
- config.example.php
- .gitignore"

echo ""
echo "✅ Repositorio Git configurado exitosamente!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Crear repositorio en GitHub:"
echo "   - Ve a https://github.com/new"
echo "   - Nombre: sistema-pagos-globocity"
echo "   - Descripción: Sistema de Control de Pagos - GloboCity"
echo "   - Público o Privado según prefieras"
echo ""
echo "2. Conectar repositorio local con GitHub:"
echo "   git remote add origin https://github.com/TU_USUARIO/sistema-pagos-globocity.git"
echo "   git branch -M main"
echo "   git push -u origin main"
echo ""
echo "3. Verificar que config.php esté en .gitignore:"
echo "   cat .gitignore | grep config.php"
echo ""
echo "🎉 ¡Listo para subir a GitHub!"
