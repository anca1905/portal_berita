import yfinance as yf
import json
import os
import time
from datetime import datetime

# Simbol Ticker Yahoo Finance
# IHSG = ^JKSE
# LQ45 tidak ada secara direct di yfinance biasanya, bisa pakai reksadana LQ45 ETF (XBLQ.JK) tapi kadang tidak realtime.
# Kita gunakan beberapa yang umum
TICKERS = {
    "IHSG": "^JKSE",
    "IDR/USD": "IDR=X",
    "IDR/EUR": "EURIDR=X",
    "Bitcoin": "BTC-USD",
    "Ethereum": "ETH-USD",
    "Emas": "GC=F",
    "Minyak WTI": "CL=F",
    "S&P 500": "^GSPC",
    "Nikkei": "^N225",
    "Hang Seng": "^HSI"
}

def fetch_data():
    market_data = []
    
    # Ambil data satu per satu (bisa dipercepat dengan yf.download() array, tapi ini lebih aman untuk individual error handling)
    for name, symbol in TICKERS.items():
        try:
            ticker = yf.Ticker(symbol)
            # Ambil history 2 hari terakhir untuk membandingkan perubahan (hari ini dan kemarin)
            hist = ticker.history(period="2d")
            
            if len(hist) > 0:
                # Harga penutupan terakhir
                current_price = hist['Close'].iloc[-1]
                
                # Menghitung perubahan
                if len(hist) > 1:
                    prev_price = hist['Close'].iloc[-2]
                    change_pct = ((current_price - prev_price) / prev_price) * 100
                else:
                    change_pct = 0.0
                    
                up = bool(change_pct >= 0)
                
                # Format string
                change_str = f"+{change_pct:.2f}%" if up else f"{change_pct:.2f}%"
                
                # Format value
                if symbol == "^JKSE" or symbol == "^GSPC" or symbol == "^N225" or symbol == "^HSI":
                    val_str = f"{current_price:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")
                elif "IDR" in symbol:
                    val_str = f"Rp {current_price:,.0f}".replace(",", ".")
                elif "USD" in symbol or "=F" in symbol:
                    val_str = f"${current_price:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")
                else:
                    val_str = f"{current_price:,.2f}"

                market_data.append({
                    "name": name,
                    "value": val_str,
                    "change": change_str,
                    "up": up
                })
            else:
                # Fallback if no data
                pass
                
        except Exception as e:
            print(f"Error fetching {name}: {e}")
            
    # Tulis hasil ke file JSON
    output_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), "market_data.json")
    
    result = {
        "updated_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "data": market_data
    }
    
    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(result, f, ensure_ascii=False, indent=2)
        
    print(f"[{datetime.now().strftime('%H:%M:%S')}] Market data updated successfully at {output_path}")

if __name__ == "__main__":
    fetch_data()
