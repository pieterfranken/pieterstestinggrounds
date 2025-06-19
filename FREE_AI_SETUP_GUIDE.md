# 🆓 FREE AI Setup Guide for PTG Quiz System

This guide shows you how to set up **completely free** AI integration for your PTG quiz system. No credit card required for most options!

## 🎯 Quick Start (5 minutes)

### Option 1: Groq (RECOMMENDED - Fastest & Easiest)

1. **Sign up** at [console.groq.com](https://console.groq.com/)
2. **Get your free API key** (100 requests/day)
3. **Add to your `.env` file:**
   ```bash
   AI_PROVIDER=groq
   GROQ_API_KEY=your_groq_api_key_here
   ```
4. **Done!** Your AI quiz generator is ready.

### Option 2: Ollama (100% FREE - Runs Locally)

1. **Download Ollama** from [ollama.ai](https://ollama.ai/)
2. **Install and run:**
   ```bash
   # Download a model (one-time setup)
   ollama pull llama2
   
   # Start Ollama (runs in background)
   ollama serve
   ```
3. **Add to your `.env` file:**
   ```bash
   AI_PROVIDER=ollama
   OLLAMA_URL=http://localhost:11434/api/generate
   OLLAMA_MODEL=llama2
   ```
4. **Benefits:** Completely free, private, no internet required after setup

## 🔧 Detailed Setup Instructions

### 🚀 Groq Setup (RECOMMENDED)

**Why Groq?**
- ✅ 100 requests/day FREE
- ✅ Very fast responses (faster than OpenAI)
- ✅ No credit card required
- ✅ Easy setup

**Steps:**
1. Go to [console.groq.com](https://console.groq.com/)
2. Sign up with email
3. Navigate to "API Keys"
4. Create new API key
5. Copy the key to your `.env`:
   ```bash
   AI_PROVIDER=groq
   GROQ_API_KEY=gsk_your_actual_key_here
   ```

### 🏠 Ollama Setup (COMPLETELY FREE)

**Why Ollama?**
- ✅ 100% free forever
- ✅ Runs on your computer
- ✅ No API limits
- ✅ Privacy-focused
- ✅ Works offline

**Steps:**
1. **Download:** Visit [ollama.ai](https://ollama.ai/) and download for your OS
2. **Install:** Run the installer
3. **Download a model:**
   ```bash
   # For general use (recommended)
   ollama pull llama2
   
   # For better quality (larger download)
   ollama pull llama2:13b
   
   # For coding questions
   ollama pull codellama
   ```
4. **Start Ollama:**
   ```bash
   ollama serve
   ```
5. **Configure `.env`:**
   ```bash
   AI_PROVIDER=ollama
   OLLAMA_URL=http://localhost:11434/api/generate
   OLLAMA_MODEL=llama2
   ```

### 🤗 HuggingFace Setup

**Why HuggingFace?**
- ✅ Free tier available
- ✅ Many model options
- ✅ Good for experimentation

**Steps:**
1. Sign up at [huggingface.co](https://huggingface.co/)
2. Go to [Settings > Access Tokens](https://huggingface.co/settings/tokens)
3. Create new token with "Read" permission
4. Add to `.env`:
   ```bash
   AI_PROVIDER=huggingface
   HUGGINGFACE_API_KEY=hf_your_token_here
   ```

### 🤝 Together AI Setup

**Why Together AI?**
- ✅ $25 free credit (no credit card)
- ✅ Multiple model options
- ✅ Good performance

**Steps:**
1. Sign up at [api.together.xyz](https://api.together.xyz/)
2. Get your API key from dashboard
3. Add to `.env`:
   ```bash
   AI_PROVIDER=together
   TOGETHER_API_KEY=your_together_key_here
   ```

## 🧪 Testing Your Setup

1. **Visit your AI quiz page:** `/ai-quiz`
2. **Enter a topic:** "JavaScript Programming"
3. **Click "Generate AI Quiz"**
4. **Check the results:**
   - ✅ Questions generated successfully
   - ✅ Multiple choice options make sense
   - ✅ AI explanations work

## 🔄 Fallback System

Even if AI fails, your quiz system will still work! We've built a smart template system:

- **Programming topics** → Get JavaScript, Python, HTML questions
- **History topics** → Get historical events, dates, figures
- **Science topics** → Get biology, chemistry, physics questions
- **Geography topics** → Get countries, capitals, landmarks
- **Generic topics** → Get general knowledge questions

## 📊 Usage Limits & Costs

| Provider | Free Limit | Cost After | Setup Time |
|----------|------------|------------|------------|
| **Groq** | 100 req/day | $0.27/1M tokens | 2 minutes |
| **Ollama** | Unlimited | $0 forever | 10 minutes |
| **HuggingFace** | 1000 req/month | Pay per use | 3 minutes |
| **Together AI** | $25 credit | $0.20/1M tokens | 3 minutes |

## 🎯 Recommended Setup for Different Users

### 🏠 Home Users / Students
**Use Ollama** - Completely free, runs on your computer
```bash
AI_PROVIDER=ollama
```

### 🏢 Small Organizations
**Use Groq** - Fast, reliable, 100 requests/day free
```bash
AI_PROVIDER=groq
```

### 🚀 Growing Projects
**Start with Groq, upgrade to Together AI** when you need more
```bash
AI_PROVIDER=groq  # Start here
# AI_PROVIDER=together  # Upgrade later
```

## 🛠️ Troubleshooting

### Groq Issues
- **"Invalid API key"** → Check your key in [console.groq.com](https://console.groq.com/)
- **"Rate limit exceeded"** → You've used your 100 daily requests

### Ollama Issues
- **"Connection refused"** → Run `ollama serve` first
- **"Model not found"** → Run `ollama pull llama2`
- **Slow responses** → Try a smaller model: `ollama pull llama2:7b`

### General Issues
- **No questions generated** → Check your `.env` file settings
- **Template questions only** → AI provider might be down, templates are working as backup

## 🎉 Success! What's Next?

Once your free AI is working:

1. **Create custom quizzes** on any topic
2. **Get AI explanations** for every answer
3. **Track your progress** with AI insights
4. **Experiment** with different topics and difficulties

## 💡 Pro Tips

1. **Mix providers:** Use Ollama for unlimited local generation, Groq for fast online queries
2. **Cache results:** Generated questions are saved, so you don't waste API calls
3. **Start small:** Test with 5 questions first, then increase
4. **Monitor usage:** Check your provider dashboards to track usage

---

**Need help?** The system includes comprehensive fallbacks, so even if AI fails, your quiz system keeps working with smart templates!
