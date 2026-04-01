AI NOTE FOR CODEX - GEMINI OPTIMIZATION

MAIN GOAL
- Optimize the shopping chatbot for Gemini-style models.
- Keep replies natural like a shop assistant.
- Minimize tokens, repeated prompt text, and unnecessary context.
- Prefer tool/data filtering first, AI wording second.

FILES TO FOCUS ON
- api/process.php
- includes/chat_widget.php
- .env only when enabling/disabling debug flags

CURRENT STATE
- Product flow already uses backend search before AI wording.
- Short memory exists: only the last AI turn is reused for follow-up messages.
- Frontend logs debug info.
- Backend can return debug.request_payload so we can inspect the exact payload sent to the model.

GEMINI-SPECIFIC RULES
1. Keep messages few.
- Best default shape:
  system
  optional short assistant memory
  user
- Do not send long multi-turn history.
 
2. Prefer structured user context.
- Gemini works better when the last user message contains clear labeled sections or compact JSON.
- Good shape:
  TASK
  CUSTOMER_REQUEST
  PARSED_PREFERENCES
  PRODUCTS
- Avoid mixing instructions and raw product text chaotically.

3. Keep the system prompt short and stable.
- Role
- tone
- constraints
- output format
- no repetition
- no long policy dumps

4. Put product data in compact structured form.
- Send only fields useful for reasoning:
  name
  type
  price
  summary
  signals
- Do not send noisy raw DB rows.
- Do not send unused fields.

5. Separate internal facts from customer-facing wording.
- AI may see:
  gender tag
  fit note
  size note
  color tags
  price
- Customer should hear:
  natural recommendation
  short explanation
  practical fit/price note if useful
- AI must not say "internal data" or dump metadata mechanically.

6. For Gemini, reduce competing instructions.
- Avoid stacking many negatives.
- Avoid repeating "do not hallucinate" in multiple places.
- Say constraints once, clearly.

7. Follow-up messages must be cheap.
- If user says:
  co
  ok
  mau nao
  gia sao
  re hon
  gui link
  size nao
- reuse only:
  last AI reply
  last suggested products summary
- never resend the whole chat log.

8. If tool-only is enough, skip model call.
- invoice lookup
- obvious redirect
- empty/no result fallback
- deterministic product card response if AI wording is not needed

PROMPT SHAPE TO PREFER
- SYSTEM:
  short role + tone + constraints + output schema

- ASSISTANT memory if follow-up:
  one short summary of the previous AI turn only

- USER:
  customer_request
  parsed_preferences
  filtered_products

Example target structure:

SYSTEM:
You are a Vietnamese shopping assistant for this store. Be natural, short, helpful, and do not invent facts. Use only the provided product context. Return JSON with reply and optional url.

ASSISTANT:
Previous recommendation: suggested product A, B, C. User is likely following up on those products.

USER:
TASK: Recommend naturally.
CUSTOMER_REQUEST: ...
PARSED_PREFERENCES: ...
PRODUCTS: [...]

WHAT TO IMPROVE NEXT
1. Refactor api/process.php.
- split into small functions:
  parse intent
  detect follow-up
  build memory hint
  build product context
  rank products
  build prompt
  build debug payload

2. Add a debug flag.
- APP_DEBUG_AI=true -> include debug.request_payload
- APP_DEBUG_AI=false -> hide debug in production

3. Add TTL for last AI memory.
- recommended: 5 to 10 minutes
- ignore stale memory

4. Reset memory on intent switch.
- product -> invoice
- clothes -> shoes
- recommendation -> order lookup
- old topic -> clearly new topic

5. Improve ranking before AI.
- audience match
- budget match
- size fit
- color match
- stock
- then relevance

6. Normalize product schema if possible.
- size_note
- color_tags
- gender_tag
- fit_note
- material
- use_case
- gift_for

Without schema, AI must guess from description text and quality will stay unstable.

7. Keep response short.
- 1 short paragraph
- max 3 products
- no long opening phrases

KNOWN PROBLEMS
1. process.php is too large and mixes many concerns.
2. Some old text still shows encoding issues.
3. Product attributes are still heuristic because DB is not structured enough.
4. Debug payload is useful for dev but expensive/noisy if always enabled.
5. Memory can still pull the search slightly off-topic if the user changes subject suddenly.

DEBUG CHECKLIST
- Always inspect:
  data.debug.request_payload
  data.debug.request_payload_stats
  data.debug.messages_text_stats
  data.debug.message_stats
- If token usage jumps, first reduce:
  number of messages
  product fields
  repeated instructions
  repeated memory

NON-NEGOTIABLE RULES
- Never send the full catalog to Gemini.
- Search/filter first, AI wording second.
- Keep system prompt stable.
- Keep memory short.
- Use structured product context.
- Do not expose raw internal metadata to the customer.
- Do not let AI invent missing size/gender/fit info.

WORK RULES FOR CODEX
- Read this file before changing AI logic.
- If a major AI change is made, update this file too.
- Do not expand scope outside AI/chat files unless the user asks.
