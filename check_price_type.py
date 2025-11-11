#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de diagnostico e correcao automatica do tipo da coluna 'price' na tabela 'products'
"""

import sqlite3
import os
import sys

# Caminho do banco de dados
db_path = "./database/database.sqlite"

if not os.path.exists(db_path):
    print(f"[ERRO] Banco de dados nao encontrado em: {db_path}")
    print("[INFO] Verifique se o caminho esta correto.")
    sys.exit(1)

try:
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Verificar estrutura da tabela
    cursor.execute("PRAGMA table_info(products);")
    
    print("[DIAGNOSTICO] Estrutura da tabela 'products':")
    print("-" * 60)
    columns = cursor.fetchall()
    price_col = None
    
    for col in columns:
        col_name = col[1]
        col_type = col[2]
        print(f"Coluna: {col_name:20} | Tipo: {col_type}")
        if col_name == "price":
            price_col = col_type
    
    print("-" * 60)
    
    # Verificar tipo da coluna price
    if price_col:
        if "INT" in price_col.upper():
            print("\n[PROBLEMA] Campo 'price' e INTEGER - isso causa o erro de multiplicacao por 100.")
            print("\n[GERANDO] Script de correcao SQL...")
            print("\n" + "=" * 60)
            print("-- SCRIPT AUTOMATICO DE CORRECAO")
            print("-- Execute este script no seu banco de dados SQLite")
            print("=" * 60)
            print("""
-- 1. Criar tabela temporaria com estrutura correta
CREATE TABLE products_new (
    id TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    category_id TEXT,
    name TEXT NOT NULL,
    description TEXT,
    image TEXT,
    status INTEGER DEFAULT 1,
    type TEXT NOT NULL,
    price REAL NOT NULL,  -- CORRIGIDO: REAL ao inves de INTEGER
    stock INTEGER DEFAULT 0,
    created_at TEXT,
    updated_at TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 2. Migrar dados convertendo price de centavos para reais
INSERT INTO products_new (
    id, user_id, category_id, name, description, image, 
    status, type, price, stock, created_at, updated_at
)
SELECT 
    id, user_id, category_id, name, description, image,
    status, type, 
    CAST(price AS REAL) / 100.0 AS price,  -- Converter centavos para reais
    stock, created_at, updated_at
FROM products;

-- 3. Verificar dados migrados
SELECT id, name, price FROM products_new LIMIT 5;

-- 4. Se estiver correto, substituir tabela antiga
-- DROP TABLE products;
-- ALTER TABLE products_new RENAME TO products;
""")
            print("=" * 60)
            print("\n[IMPORTANTE] Revise os dados antes de executar o DROP TABLE!")
        else:
            print(f"\n[OK] Campo 'price' esta correto: {price_col}")
            print("     O tipo esta adequado para armazenar valores decimais.")
    else:
        print("\n[ERRO] Campo 'price' nao encontrado na tabela 'products'.")
    
    # Testar valores existentes
    print("\n[AMOSTRA] Valores atuais na tabela:")
    print("-" * 60)
    try:
        cursor.execute("SELECT id, name, price FROM products LIMIT 5;")
        rows = cursor.fetchall()
        if rows:
            for row in rows:
                product_id = row[0][:8] + "..." if len(row[0]) > 8 else row[0]
                name = row[1][:30] + "..." if len(row[1]) > 30 else row[1]
                price = row[2]
                print(f"ID: {product_id:15} | Nome: {name:35} | Preco: {price}")
        else:
            print("Nenhum produto encontrado na tabela.")
    except Exception as e:
        print(f"Erro ao consultar produtos: {e}")
    
    print("-" * 60)
    
    conn.close()
    print("\n[CONCLUIDO] Diagnostico finalizado!")
    
except sqlite3.Error as e:
    print(f"[ERRO] Erro ao acessar banco de dados: {e}")
    sys.exit(1)
except Exception as e:
    print(f"[ERRO] Erro inesperado: {e}")
    sys.exit(1)

