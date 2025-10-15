// DEPENDENCIES: express, cors, @google/generative-ai
// npm install express cors @google/generative-ai

const express = require('express');
const cors = require('cors');
const { GoogleGenerativeAI } = require('@google/generative-ai');



const app = express();
app.use(cors());
app.use(express.json());

let genAI;
let model;

// Initialize Gemini
try {
  genAI = new GoogleGenerativeAI('AIzaSyAxLmxUMRko0hhq9Trd723BwGVpiVdqyEk');
  model = genAI.getGenerativeModel({ model: 'gemini-2.5-flash' });
} catch (error) {
  console.error('Failed to initialize Gemini AI:', error);
}

// Conversation memory (temporary, resets when server restarts)
let conversationHistory = [];

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'Server is running!', aiReady: !!model });
});


// Chat endpoint with context memory
// Example: endpoint to use file as context
app.post('/chat-with-context', async (req, res) => {
  try {
    const { message, context } = req.body;

    if (!context || !message) {
      return res.status(400).json({ error: 'Both context and message are required.' });
    }

    // Build the full prompt for Gemini
    const prompt = `
You are an AI assistant that must only answer using the following document context.
If the answer cannot be found, respond with:
"I’m sorry, I don’t have enough information in the provided document to answer that."

Document Content:
${context}

User Question:
${message}
`;

    console.log("🤖 Sending prompt to Gemini...");

    // ✅ Correct Gemini SDK usage
    const result = await model.generateContent(prompt);

    // Extract reply safely
    const reply =
      result?.response?.text() ||
      result?.response?.candidates?.[0]?.content?.parts?.[0]?.text ||
      'No response generated.';

    console.log("✅ Gemini replied:", reply.slice(0, 200), "...");
    res.json({ reply });
  } catch (error) {
    console.error("💥 Chat-with-context error:", error);
    res.status(500).json({ error: 'AI service unavailable.' });
  }
});



// 404 Handler
app.use((req, res) => {
  res.status(404).json({ error: 'Endpoint not found' });
});

// Start server
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
  console.log('Health check: http://localhost:3000/health');
  console.log('Chat endpoint: POST http://localhost:3000/chat');
});