import fs from 'fs';
import path from 'path';

const cssDir = path.join(process.cwd(), 'resources/css');

function splitShadows(value) {
    let result = [];
    let current = '';
    let parens = 0;
    for (let i = 0; i < value.length; i++) {
        let char = value[i];
        if (char === '(') parens++;
        if (char === ')') parens--;
        if (char === ',' && parens === 0) {
            result.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }
    if (current) result.push(current.trim());
    return result;
}

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');

    const regex = /(box-shadow\s*:\s*)([^;]+?)(;|\s*!important\s*;)/gi;

    content = content.replace(regex, (match, prefix, value, suffix) => {
        if (value.trim() === 'none' || value.includes('var(--shadow') || value.trim().startsWith('var(')) {
            return match;
        }

        const shadows = splitShadows(value);
        const newShadows = shadows.map(shadow => {
            let parens = 0;
            let tokens = [];
            let currentToken = '';
            
            for(let i=0; i<shadow.length; i++) {
               let char = shadow[i];
               if(char === '(') parens++;
               if(char === ')') parens--;
               
               if(/\s/.test(char) && parens === 0) {
                   if(currentToken) tokens.push(currentToken);
                   currentToken = '';
               } else {
                   currentToken += char;
               }
            }
            if(currentToken) tokens.push(currentToken);
            
            const isLength = (t) => /^-?\d+(?:\.\d+)?(?:px|em|rem|vw|vh|vmin|vmax|%)?$|^0$/.test(t);
            
            let colorTokens = [];
            let hasInset = false;
            for(const t of tokens) {
                if(t === 'inset') {
                    hasInset = true;
                } else if(!isLength(t)) {
                    colorTokens.push(t);
                }
            }
            
            let res = hasInset ? "inset " : "";
            res += "0 5px 8px";
            if (colorTokens.length > 0) {
                res += " " + colorTokens.join(' ');
            }
            return res.trim();
        });

        const newValue = newShadows.join(', ');
        return prefix + newValue + suffix;
    });

    fs.writeFileSync(filePath, content, 'utf8');
}

function processDirectory(dir) {
    if (!fs.existsSync(dir)) return;
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            processDirectory(fullPath);
        } else if (fullPath.endsWith('.css')) {
            processFile(fullPath);
        }
    }
}

processDirectory(cssDir);
console.log("Done updating box shadows.");
